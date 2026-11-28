(function () {
  'use strict';
  var options = window.vpscsSoftwareAdmin || {};
  var page, form, config = {}, users = [], userPage = 1, timer, groupOpen = true;
  var perPage = 10;

  function escapeHtml(value) { var node = document.createElement('div'); node.textContent = value == null ? '' : String(value); return node.innerHTML; }
  function token() {
    var value = localStorage.getItem('Xboard_access_token') || '';
    try { value = JSON.parse(value); } catch (_) {}
    return String(value || '');
  }
  function api(path, init) {
    init = init || {};
    init.headers = Object.assign({ 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': token() }, init.headers || {});
    return fetch(options.apiBase + '/' + path, init).then(async function (response) {
      var payload = await response.json().catch(function () { return {}; });
      if (!response.ok) throw new Error(payload.message || payload.error || '请求失败（' + response.status + '）');
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
      update_enabled: checked('update_enabled'), latest_version: value('latest_version'), windows_download_url: value('windows_download_url'), macos_download_url: value('macos_download_url'), update_notes: value('update_notes'), force_update: checked('force_update')
    };
  }
  function fill(data) {
    config = data || {};
    Object.keys(config).forEach(function (key) { var node = form.elements[key]; if (!node) return; if (node.type === 'checkbox') node.checked = !!config[key]; else node.value = config[key] == null ? '' : config[key]; });
  }
  async function loadConfig() { setStatus('正在加载软件配置…'); try { fill(await api('config')); setStatus(''); } catch (error) { setStatus('❌ ' + error.message); } }
  async function saveConfig(publishPopup) {
    var data = payload(); if (publishPopup) { data.popup_enabled = true; data.popup_version = String(Date.now()); }
    setStatus('正在保存…');
    try { fill(await api('config', { method: 'POST', body: JSON.stringify(data) })); setStatus('✅ 软件配置已保存'); }
    catch (error) { setStatus('❌ ' + error.message); }
  }
  function dateText(value) { return value ? new Date(value).toLocaleString('zh-CN', { hour12: false }) : '-'; }
  function renderTelemetry() {
    var online = users.filter(function (item) { return item.online; }).length;
    page.querySelector('[data-online-count]').textContent = online;
    page.querySelector('[data-user-count]').textContent = users.length;
    var userPages = Math.max(1, Math.ceil(users.length / perPage)); userPage = Math.min(userPage, userPages);
    var userRows = users.slice((userPage - 1) * perPage, userPage * perPage).map(function (item) {
      return '<div class="vpscs-sw-row"><div class="vpscs-sw-row-head"><strong>' + escapeHtml(item.user_remark || '未设置备注') + '</strong><span class="' + (item.online ? 'vpscs-sw-online' : 'vpscs-sw-offline') + '">' + (item.online ? '● 在线' : '● 离线') + '</span></div><div>' + escapeHtml(item.user_identifier) + '</div><div class="vpscs-sw-muted">IP：' + escapeHtml(item.ip_address || '-') + ' · 版本：' + escapeHtml(item.app_version || '-') + '</div><div class="vpscs-sw-muted">节点：' + escapeHtml(item.current_node || '未连接') + '</div><div class="vpscs-sw-muted">最后活跃：' + escapeHtml(dateText(item.last_seen)) + '</div></div>';
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
      '<section class="vpscs-sw-card"><div class="vpscs-sw-title"><h2>顶部滚动公告</h2><label class="vpscs-sw-check"><input name="notice_enabled" type="checkbox">启用</label></div><label class="vpscs-sw-field">公告内容<textarea name="notice" rows="5" maxlength="300"></textarea></label></section>' +
      '<section class="vpscs-sw-card"><div class="vpscs-sw-title"><h2>登录用户弹窗公告</h2><label class="vpscs-sw-check"><input name="popup_enabled" type="checkbox">启用</label></div><label class="vpscs-sw-field">弹窗内容<textarea name="popup_content" rows="5" maxlength="1000"></textarea></label><label class="vpscs-sw-field">每位用户最多显示次数<input name="popup_display_count" type="number" min="1" max="20" value="1"></label><input name="popup_version" type="hidden"><div class="vpscs-sw-actions"><button type="button" class="vpscs-sw-btn" data-publish-popup>重新发布弹窗</button></div></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>软件接口域名</h2></div><label class="vpscs-sw-field">只填写域名，不填写协议或路径<input name="api_domain" placeholder="www.example.com"></label></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>软件版本更新</h2><label class="vpscs-sw-check"><input name="update_enabled" type="checkbox">启用更新</label></div><div class="vpscs-sw-grid"><label class="vpscs-sw-field">最新版本号<input name="latest_version" placeholder="0.3.3"></label><label class="vpscs-sw-check"><input name="force_update" type="checkbox">强制更新</label><label class="vpscs-sw-field is-wide">Windows 安装包 HTTPS 地址<input name="windows_download_url" type="url"></label><label class="vpscs-sw-field is-wide">macOS 安装包 HTTPS 地址<input name="macos_download_url" type="url"></label><label class="vpscs-sw-field is-wide">更新说明<textarea name="update_notes" rows="5" maxlength="3000"></textarea></label></div></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>客户端接口</h2></div><div class="vpscs-sw-code">GET ' + escapeHtml(location.origin + '/api/v1/guest/software/config') + '</div><div class="vpscs-sw-code" style="margin-top:10px">POST ' + escapeHtml(location.origin + '/api/v1/guest/software/telemetry') + '</div></section>' +
      '<div class="vpscs-sw-actions"><button type="submit" class="vpscs-sw-btn is-primary">保存全部软件配置</button></div></div></form>' +
      '<section class="vpscs-sw-card" style="margin-top:22px"><div class="vpscs-sw-title"><h2>软件使用情况</h2></div><div class="vpscs-sw-stats"><div class="vpscs-sw-stat">当前在线<strong data-online-count>0</strong></div><div class="vpscs-sw-stat">登录过的软件用户<strong data-user-count>0</strong></div></div><div class="vpscs-sw-list"><h3>软件用户在线状态</h3><div data-users></div><div class="vpscs-sw-pager"><button class="vpscs-sw-btn" data-user-prev>上一页</button><span data-user-page>1 / 1</span><button class="vpscs-sw-btn" data-user-next>下一页</button></div></div></section></div>';
    document.body.appendChild(page); form = page.querySelector('[data-form]');
    form.addEventListener('submit', function (event) { event.preventDefault(); saveConfig(false); });
    page.addEventListener('click', function (event) {
      var button = event.target.closest('button'); if (!button) return; event.preventDefault();
      if (button.matches('[data-refresh]')) { Promise.all([loadConfig(), loadTelemetry()]); }
      if (button.matches('[data-publish-popup]')) saveConfig(true);
      if (button.matches('[data-user-prev]')) { userPage = Math.max(1, userPage - 1); renderTelemetry(); }
      if (button.matches('[data-user-next]')) { userPage++; renderTelemetry(); }
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

  function syncRoute() {
    var active = /#\/software-management(?:\?|$)/.test(location.hash);
    page.classList.toggle('is-active', active);
    var group = softwareGroup();
    var link = group && group.querySelector('[data-vpscs-software-link]');
    if (link) {
      link.classList.toggle('bg-secondary', active);
      link.classList.toggle('text-secondary-foreground', active);
      link.classList.toggle('shadow-sm', active);
      if (active) link.setAttribute('aria-current', 'page'); else link.removeAttribute('aria-current');
      if (active && !groupOpen) { groupOpen = true; applyGroupState(group); }
    }
    if (active) { loadConfig(); loadTelemetry(); clearInterval(timer); timer = setInterval(function () { loadTelemetry(true); }, 5000); }
    else { clearInterval(timer); timer = null; }
  }
  function boot() {
    createPage();
    installMenu();
    var root = document.getElementById('root');
    if (root) {
      var pending = false;
      new MutationObserver(function () {
        if (pending) return;
        pending = true;
        requestAnimationFrame(function () { pending = false; installMenu(); });
      }).observe(root, { childList: true, subtree: true, attributes: true, attributeFilter: ['data-collapsed'] });
    }
    addEventListener('hashchange', function () { installMenu(); syncRoute(); });
    syncRoute();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true }); else boot();
})();
