(function () {
  'use strict';
  var options = window.vpscsSoftwareAdmin || {};
  var page, form, config = {}, users = [], userPage = 1, timer, groupOpen = true;
  var perPage = 10;

  function escapeHtml(value) { var node = document.createElement('div'); node.textContent = value == null ? '' : String(value); return node.innerHTML; }
  function token() {
    // 后台把登录票据存在 VUE_NAIVE_ACCESS_TOKEN（值是 {value:'xx|yy'}），
    // 旧版本叫 XBOARD_ACCESS_TOKEN，两种都认。
    var keys = Object.keys(localStorage);
    for (var i = 0; i < keys.length; i += 1) {
      if (!/(xboard|vue_naive)[_-]?access[_-]?token$/i.test(keys[i])) continue;
      var raw = localStorage.getItem(keys[i]) || '';
      var parsed = raw;
      try { parsed = JSON.parse(raw); } catch (_) {}
      if (parsed && typeof parsed === 'object') parsed = parsed.value || parsed.token || '';
      var value = String(parsed || '').trim();
      if (value) return value;
    }
    return '';
  }
  function authHeader() {
    var value = token();
    if (!value) return '';
    return /^Bearer\s/i.test(value) ? value : 'Bearer ' + value;
  }
  function api(path, init) {
    init = init || {};
    init.headers = Object.assign({ 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': authHeader() }, init.headers || {});
    return fetch(options.apiBase + '/' + path, init).then(async function (response) {
      var payload = await response.json().catch(function () { return {}; });
      if (!response.ok) {
        var message = payload.message || payload.error || '请求失败（' + response.status + '）';
        // Laravel 的 422 只给 rule key（validation.regex 之类），把首个字段报错抖出来。
        if (payload.errors && typeof payload.errors === 'object') {
          var field = Object.keys(payload.errors)[0];
          var detail = field && payload.errors[field] && payload.errors[field][0];
          if (detail && detail !== message) message = detail;
        }
        throw new Error(message);
      }
      return Object.prototype.hasOwnProperty.call(payload, 'data') ? payload.data : payload;
    });
  }
  function setStatus(message) { var node = page && page.querySelector('[data-status]'); if (node) node.textContent = message || ''; }
  function checked(name) { var node = form.elements[name]; return !!(node && node.checked); }
  function value(name) { var node = form.elements[name]; return node ? node.value.trim() : ''; }
  function payload() {
    return {
      notice_enabled: checked('notice_enabled'), notice: value('notice'), api_domain: value('api_domain'),
      popup_enabled: checked('popup_enabled'), popup_content: value('popup_content'), popup_version: value('popup_version'), popup_display_count: Number(value('popup_display_count')) || 1,
      update_enabled: checked('update_enabled'), latest_version: value('latest_version'), windows_download_url: value('windows_download_url'), macos_download_url: value('macos_download_url'), update_notes: value('update_notes'), force_update: checked('force_update'),
      // 应急节点界面已隐藏，但后端仍要求这两个字段；保存其他分区时保留数据库原值。
      emergency_nodes_enabled: !!config.emergency_nodes_enabled, emergency_subscription_url: String(config.emergency_subscription_url || '')
    };
  }
  function fill(data) {
    config = data || {};
    Object.keys(config).forEach(function (key) { var node = form.elements[key]; if (!node) return; if (node.type === 'checkbox') node.checked = !!config[key]; else node.value = config[key] == null ? '' : config[key]; });
  }
  async function loadConfig() { setStatus('正在加载软件配置…'); try { fill(await api('config')); setStatus(''); } catch (error) { setStatus('❌ ' + error.message); } }
  function setButtonStatus(button, message, busy) {
    if (!button) return;
    if (!button.dataset.defaultText) button.dataset.defaultText = button.textContent;
    button.disabled = !!busy;
    button.textContent = message;
    if (!busy) window.setTimeout(function () { button.textContent = button.dataset.defaultText; }, 2600);
  }
  async function saveConfig(publishPopup, button) {
    var data = payload(); if (publishPopup) { data.popup_enabled = true; data.popup_version = String(Date.now()); }
    setStatus('正在保存…'); setButtonStatus(button, '保存中…', true);
    try {
      fill(await api('config', { method: 'POST', body: JSON.stringify(data) }));
      setStatus('✅ 软件配置已保存'); setButtonStatus(button, '✓ 保存成功', false);
    } catch (error) {
      setStatus('❌ ' + error.message); setButtonStatus(button, '保存失败', false);
    }
  }
  function dateText(value) { return value ? new Date(value).toLocaleString('zh-CN', { hour12: false }) : '-'; }
  function renderTelemetry() {
    var online = users.filter(function (item) { return item.online; }).length;
    page.querySelector('[data-online-count]').textContent = online;
    page.querySelector('[data-user-count]').textContent = users.length;
    var userPages = Math.max(1, Math.ceil(users.length / perPage)); userPage = Math.min(userPage, userPages);
    var userRows = users.slice((userPage - 1) * perPage, userPage * perPage).map(function (item) {
      return '<div class="vpscs-sw-row"><div class="vpscs-sw-row-head"><strong>' + escapeHtml(item.user_remark || '未设置备注') + '</strong><div><span class="' + (item.online ? 'vpscs-sw-online' : 'vpscs-sw-offline') + '">' + (item.online ? '● 在线' : '● 离线') + '</span><button type="button" class="vpscs-sw-btn is-danger" data-delete-user="' + escapeHtml(item.id) + '" data-delete-user-name="' + escapeHtml(item.user_remark || item.user_identifier || '该用户') + '">删除</button></div></div><div>' + escapeHtml(item.user_identifier) + '</div><div class="vpscs-sw-muted">IP：' + escapeHtml(item.ip_address || '-') + ' · 版本：' + escapeHtml(item.app_version || '-') + '</div><div class="vpscs-sw-muted">节点：' + escapeHtml(item.current_node || '未连接') + '</div><div class="vpscs-sw-muted">最后活跃：' + escapeHtml(dateText(item.last_seen)) + '</div></div>';
    }).join('') || '<div class="vpscs-sw-row vpscs-sw-muted">还没有软件用户记录</div>';
    page.querySelector('[data-users]').innerHTML = userRows;
    page.querySelector('[data-user-page]').textContent = userPage + ' / ' + userPages;
  }
  async function loadTelemetry(quiet) {
    if (!quiet) setStatus('正在读取软件使用情况…');
    try { var data = await api('telemetry'); users = data.users || []; renderTelemetry(); if (!quiet) setStatus(''); }
    catch (error) { if (!quiet) setStatus('❌ ' + error.message); }
  }
  function createPage() {
    page = document.createElement('main'); page.id = 'vpscs-software-page';
    page.innerHTML = '<div class="vpscs-sw-shell"><header class="vpscs-sw-head"><div><h1>软件管理</h1><p class="vpscs-sw-muted">管理客户端公告、版本更新和使用情况。</p></div><button class="vpscs-sw-btn" data-refresh>刷新数据</button></header><div class="vpscs-sw-status" data-status></div><form data-form><div class="vpscs-sw-grid">' +
      '<section class="vpscs-sw-card"><div class="vpscs-sw-title"><h2>顶部滚动公告</h2><label class="vpscs-sw-check"><input name="notice_enabled" type="checkbox">启用</label></div><label class="vpscs-sw-field">公告内容<textarea name="notice" rows="5" maxlength="300"></textarea></label><div class="vpscs-sw-actions"><button type="button" class="vpscs-sw-btn is-primary" data-save-section="notice">保存滚动公告</button></div></section>' +
      '<section class="vpscs-sw-card"><div class="vpscs-sw-title"><h2>登录用户弹窗公告</h2><label class="vpscs-sw-check"><input name="popup_enabled" type="checkbox">启用</label></div><label class="vpscs-sw-field">弹窗内容<textarea name="popup_content" rows="5" maxlength="1000"></textarea></label><label class="vpscs-sw-field">每位用户最多显示次数<input name="popup_display_count" type="number" min="1" max="20" value="1"></label><input name="popup_version" type="hidden"><div class="vpscs-sw-actions"><button type="button" class="vpscs-sw-btn is-primary" data-save-section="popup">保存弹窗设置</button><button type="button" class="vpscs-sw-btn" data-publish-popup>重新发布弹窗</button></div></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>软件接口域名</h2></div><label class="vpscs-sw-field">只填写域名，不填写协议或路径<input name="api_domain" placeholder="www.example.com"></label><p class="vpscs-sw-muted">换域名时在这里填写新域名并保存；请先确保新域名已部署相同软件接口且 HTTPS 正常。</p><div class="vpscs-sw-actions"><button type="button" class="vpscs-sw-btn is-primary" data-save-section="domain">保存接口域名</button></div></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>软件版本更新</h2><label class="vpscs-sw-check"><input name="update_enabled" type="checkbox">启用更新</label></div><div class="vpscs-sw-grid"><label class="vpscs-sw-field">最新版本号<input name="latest_version" placeholder="0.3.3"></label><label class="vpscs-sw-check"><input name="force_update" type="checkbox">强制更新</label><label class="vpscs-sw-field is-wide">Windows 安装包 HTTPS 地址<input name="windows_download_url" type="url"></label><label class="vpscs-sw-field is-wide">macOS 安装包 HTTPS 地址<input name="macos_download_url" type="url"></label><label class="vpscs-sw-field is-wide">更新说明<textarea name="update_notes" rows="5" maxlength="3000"></textarea></label></div><div class="vpscs-sw-actions"><button type="button" class="vpscs-sw-btn is-primary" data-save-section="update">保存版本更新</button></div></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>客户端接口</h2></div><div class="vpscs-sw-code">GET ' + escapeHtml(location.origin + '/api/v1/guest/software/config') + '</div><div class="vpscs-sw-code" style="margin-top:10px">POST ' + escapeHtml(location.origin + '/api/v1/guest/software/telemetry') + '</div></section>' +
      '<div class="vpscs-sw-actions"><button type="submit" class="vpscs-sw-btn is-primary">保存全部软件配置</button></div></div></form>' +
      '<section class="vpscs-sw-card" style="margin-top:22px"><div class="vpscs-sw-title"><h2>软件使用情况</h2></div><div class="vpscs-sw-stats"><div class="vpscs-sw-stat">当前在线<strong data-online-count>0</strong></div><div class="vpscs-sw-stat">登录过的软件用户<strong data-user-count>0</strong></div></div><div class="vpscs-sw-list"><h3>软件用户在线状态</h3><div data-users></div><div class="vpscs-sw-pager"><button class="vpscs-sw-btn" data-user-prev>上一页</button><span data-user-page>1 / 1</span><button class="vpscs-sw-btn" data-user-next>下一页</button></div></div></section></div>';
    document.body.appendChild(page); form = page.querySelector('[data-form]');
    form.addEventListener('submit', function (event) { event.preventDefault(); saveConfig(false, form.querySelector('button[type="submit"]')); });
    page.addEventListener('click', async function (event) {
      var button = event.target.closest('button'); if (!button) return; event.preventDefault();
      if (button.matches('[data-refresh]')) { Promise.all([loadConfig(), loadTelemetry()]); }
      if (button.matches('[data-save-section]')) saveConfig(false, button);
      if (button.matches('[data-publish-popup]')) saveConfig(true, button);
      if (button.matches('[data-user-prev]')) { userPage = Math.max(1, userPage - 1); renderTelemetry(); }
      if (button.matches('[data-user-next]')) { userPage++; renderTelemetry(); }
      if (button.matches('[data-delete-user]')) {
        var userName = button.dataset.deleteUserName || '该用户';
        if (!window.confirm('确定删除“' + userName + '”的软件在线记录吗？\n用户以后再次打开软件时会重新出现在列表中。')) return;
        setButtonStatus(button, '删除中…', true);
        try {
          await api('user/delete', { method: 'POST', body: JSON.stringify({ id: button.dataset.deleteUser }) });
          setStatus('✅ 软件用户记录已删除');
          await loadTelemetry(true);
        } catch (error) {
          setStatus('❌ ' + error.message);
          setButtonStatus(button, '删除失败', false);
        }
      }
    });
  }

  // ---- 侧边栏：把「软件管理」提升为与「节点管理 / 订阅管理」并列的一级分组 ----
  var ICON_GROUP = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M10 4v4"/><path d="M2 8h20"/></svg>';
  var ICON_ITEM = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>';

  // 侧边栏的 id="sidebar-menu" 只被用作 aria-controls，并未真正渲染到 DOM 上，
  // 所以这里按结构定位：唯一的 aside > nav。
  function navElement() { return document.querySelector('aside nav') || document.getElementById('sidebar-menu'); }
  function softwareGroup() { return document.querySelector('[data-vpscs-software-group]'); }
  function isCollapsed(nav) {
    var holder = nav.closest('[data-collapsed]');
    return !!(holder && holder.getAttribute('data-collapsed') === 'true');
  }
  function setText(root, text) {
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null), node;
    while ((node = walker.nextNode())) {
      if (/\S/.test(node.nodeValue)) { node.nodeValue = text; return true; }
    }
    return false;
  }
  function clearBadge(root) {
    var badge = root.querySelector('.bg-primary');
    if (badge && !badge.querySelector('svg')) badge.remove();
  }
  function applyGroupState(group) {
    var button = group.querySelector('button');
    var content = group.querySelector('.collapsibleDropdown');
    if (button) { button.setAttribute('data-state', groupOpen ? 'open' : 'closed'); button.setAttribute('aria-expanded', groupOpen ? 'true' : 'false'); }
    if (content) {
      content.setAttribute('data-state', groupOpen ? 'open' : 'closed');
      if (groupOpen) {
        content.removeAttribute('hidden');
        content.style.removeProperty('display');
        // 克隆/构造出来的节点没有 Radix 注入的 --radix-collapsible-content-height，
        // slideDown 动画会把高度算成 0，这里直接关掉动画并固定为自适应高度。
        content.style.animation = 'none';
        content.style.height = 'auto';
      } else { content.setAttribute('hidden', ''); content.style.display = 'none'; }
    }
  }
  var BASE_BTN = 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground';
  var CHEVRON = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';

  function decorateGroup(group) {
    var button = group.querySelector('button');
    var content = group.querySelector('.collapsibleDropdown');
    var list = content && content.querySelector('ul');
    if (!button || !list) return false;

    button.removeAttribute('aria-controls');
    clearBadge(button);
    setText(button, '软件管理');
    var groupIcon = button.querySelector('.mr-2');
    if (groupIcon) groupIcon.innerHTML = ICON_GROUP;

    Array.prototype.slice.call(list.children).forEach(function (li, index) { if (index > 0) li.remove(); });
    var link = list.querySelector('a');
    if (!link) return false;
    link.setAttribute('href', '#/software-management');
    link.setAttribute('data-vpscs-software-link', '1');
    link.removeAttribute('aria-current');
    link.classList.remove('bg-secondary', 'text-secondary-foreground', 'shadow-sm');
    clearBadge(link);
    setText(link, '软件管理');
    var itemIcon = link.querySelector('.mr-2');
    if (itemIcon) itemIcon.innerHTML = ICON_ITEM;
    return true;
  }
  function buildFallbackGroup() {
    var group = document.createElement('div');
    group.setAttribute('data-vpscs-software-group', '1');
    group.setAttribute('data-state', 'open');
    group.className = 'vpscs-software-group';
    group.innerHTML = '<button type="button" class="' + BASE_BTN + ' group h-12 w-full justify-start rounded-none px-6" data-state="open" aria-expanded="true">'
      + '<div class="mr-2">' + ICON_GROUP + '</div>软件管理<span class="ml-auto transition-all group-data-[state=open]:-rotate-180">' + CHEVRON + '</span></button>'
      + '<div class="collapsibleDropdown" data-state="open"><ul><li class="my-1 ml-8">'
      + '<a class="' + BASE_BTN + ' h-10 w-full border-l border-l-slate-500 px-2" href="#/software-management" data-vpscs-software-link="1"><div class="mr-2">' + ICON_ITEM + '</div>软件管理</a>'
      + '</li></ul></div>';
    return group;
  }
  function installGroup(nav) {
    if (softwareGroup()) return;
    var items = Array.prototype.slice.call(nav.children);
    var groups = items.filter(function (el) { return el.tagName === 'DIV' && el.querySelector('button'); });
    var anchor = groups.filter(function (el) { return /用户管理/.test(el.textContent || ''); })[0] || groups[groups.length - 1] || null;
    // 优先克隆「已展开且未选中」的分组，避免把选中态和高亮一并复制过来
    var template = groups.filter(function (el) { return el.querySelector('ul li a') && !el.querySelector('[aria-current="page"]'); })[0]
      || groups.filter(function (el) { return el.querySelector('ul li a'); })[0];

    var group;
    if (template) {
      group = template.cloneNode(true);
      group.setAttribute('data-vpscs-software-group', '1');
      group.classList.add('vpscs-software-group');
      group.removeAttribute('id');
      Array.prototype.slice.call(group.querySelectorAll('[id]')).forEach(function (node) { node.removeAttribute('id'); });
      if (!decorateGroup(group)) group = null;
    }
    if (!group) {
      // 所有分组都被折叠时模板里没有子项，直接按同一套类名构造
      group = buildFallbackGroup();
      if (!decorateGroup(group)) return;
    }

    var button = group.querySelector('button');
    var content = group.querySelector('.collapsibleDropdown');
    if (!button || !content) return;
    button.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      groupOpen = !groupOpen;
      applyGroupState(group);
    });

    if (anchor && anchor.parentElement === nav) anchor.insertAdjacentElement('afterend', group);
    else nav.appendChild(group);
    applyGroupState(group);
  }
  function installCollapsed(nav) {
    if (nav.querySelector('[data-vpscs-software-collapsed]')) return;
    var link = document.createElement('a');
    link.href = '#/software-management';
    link.title = '软件管理';
    link.setAttribute('aria-label', '软件管理');
    link.setAttribute('data-vpscs-software-collapsed', '1');
    link.className = 'vpscs-software-collapsed';
    link.innerHTML = ICON_GROUP;
    nav.appendChild(link);
  }
  function installMenu() {
    var nav = navElement();
    if (!nav) return;
    if (isCollapsed(nav)) {
      var group = softwareGroup();
      if (group) group.remove();
      installCollapsed(nav);
      return;
    }
    var leftover = nav.querySelector('[data-vpscs-software-collapsed]');
    if (leftover) leftover.remove();
    installGroup(nav);
  }

  var softwareActive = false;
  var pendingActivate = false;
  var lastRoute = '';
  var softwareRoute = '';

  function rememberRoute() {
    var hash = location.hash || '';
    if (!hash || /#\/software-management/.test(hash)) return;
    lastRoute = hash;
    try { sessionStorage.setItem('vpscs_last_admin_route', hash); } catch (_) {}
  }

  function updateActiveLink() {
    var group = softwareGroup();
    var link = group && group.querySelector('[data-vpscs-software-link]');
    if (!link) return;
    link.classList.toggle('bg-secondary', softwareActive);
    link.classList.toggle('text-secondary-foreground', softwareActive);
    link.classList.toggle('shadow-sm', softwareActive);
    if (!softwareActive) { link.removeAttribute('aria-current'); return; }
    link.setAttribute('aria-current', 'page');
    if (!groupOpen) { groupOpen = true; applyGroupState(group); }
    // SPA 会把当前路由对应的入口标成选中，这里让位给「软件管理」。
    Array.prototype.slice.call(document.querySelectorAll('aside nav a[aria-current="page"]')).forEach(function (node) {
      if (node === link) return;
      node.removeAttribute('aria-current');
      node.classList.remove('bg-secondary', 'text-secondary-foreground', 'shadow-sm');
    });
  }

  function setActive(flag) {
    if (!!flag === softwareActive) {
      if (page) page.classList.toggle('is-active', softwareActive);
      updateActiveLink();
      return;
    }
    softwareActive = !!flag;
    if (!softwareActive) softwareRoute = '';
    if (page) page.classList.toggle('is-active', softwareActive);
    document.body.classList.toggle('vpscs-software-open', softwareActive);
    updateActiveLink();
    if (softwareActive) {
      loadConfig();
      loadTelemetry();
      clearInterval(timer);
      timer = setInterval(function () { loadTelemetry(true); }, 5000);
    } else if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  // 后台路由是编译期写死的，并不存在 /software-management：直接改 hash 会落到 404 页，
  // 整个左侧导航一起被替换掉。所以这里不改路由，改成覆盖在内容区的面板。
  function openSoftware() {
    if (/#\/software-management/.test(location.hash) || !navElement()) {
      // 书签 / 刷新仍带着旧 hash：先回到上一个真实路由，让 SPA 重新渲染侧边栏。
      pendingActivate = true;
      location.hash = lastRoute || '#/user/manage';
      return;
    }
    softwareRoute = location.hash || '';
    setActive(true);
  }

  function handleHashChange() {
    installMenu();
    var hash = location.hash || '';
    if (/#\/software-management/.test(hash)) { openSoftware(); return; }
    rememberRoute();
    if (pendingActivate) {
      pendingActivate = false;
      softwareRoute = hash;
      setActive(true);
      return;
    }
    // 面板开着时，路由没变就保持（React Router 会补一次 replaceState）。
    if (softwareActive && hash === softwareRoute) { updateActiveLink(); return; }
    setActive(false);
  }

  // React Router 用 history.pushState 切换路由，不会触发 hashchange，
  // 所以这里补一层拦截，保证点别的菜单时面板会自己收起。
  function watchHistory() {
    ['pushState', 'replaceState'].forEach(function (name) {
      var original = history[name];
      if (!original || original.__vpscsPatched) return;
      var wrapped = function () {
        var result = original.apply(this, arguments);
        setTimeout(handleHashChange, 0);
        return result;
      };
      wrapped.__vpscsPatched = true;
      history[name] = wrapped;
    });
    addEventListener('popstate', handleHashChange);
  }

  function boot() {
    try { lastRoute = sessionStorage.getItem('vpscs_last_admin_route') || ''; } catch (_) {}
    createPage();
    installMenu();
    var root = document.getElementById('root');
    if (root) {
      var pending = false;
      new MutationObserver(function () {
        if (pending) return;
        pending = true;
        requestAnimationFrame(function () { pending = false; installMenu(); if (softwareActive) updateActiveLink(); });
      }).observe(root, { childList: true, subtree: true, attributes: true, attributeFilter: ['data-collapsed'] });
    }
    document.addEventListener('click', function (event) {
      var link = event.target.closest && event.target.closest('a[href="#/software-management"], [data-vpscs-software-link]');
      if (!link) {
        // 侧边栏上任何别的入口都算离开软件管理，立刻把面板收起来，
        // 不依赖前端路由是否触发 hashchange / pushState。
        if (softwareActive && event.target.closest && event.target.closest('aside nav a, aside nav button[data-state]')) setActive(false);
        return;
      }
      event.preventDefault();
      event.stopPropagation();
      openSoftware();
    }, true);
    addEventListener('hashchange', handleHashChange);
    watchHistory();
    rememberRoute();
    if (/#\/software-management/.test(location.hash)) openSoftware();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true }); else boot();
})();
