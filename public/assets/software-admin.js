(function () {
  'use strict';
  var options = window.vpscsSoftwareAdmin || {};
  var page, form, config = {}, users = [], logs = [], userPage = 1, logPage = 1, timer;
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
      update_enabled: checked('update_enabled'), latest_version: value('latest_version'), windows_download_url: value('windows_download_url'), macos_download_url: value('macos_download_url'), update_notes: value('update_notes'), force_update: checked('force_update'),
      emergency_nodes_enabled: checked('emergency_nodes_enabled'), emergency_subscription_url: value('emergency_subscription_url')
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
    page.querySelector('[data-log-count]').textContent = logs.length;
    var userPages = Math.max(1, Math.ceil(users.length / perPage)); userPage = Math.min(userPage, userPages);
    var logPages = Math.max(1, Math.ceil(logs.length / perPage)); logPage = Math.min(logPage, logPages);
    var userRows = users.slice((userPage - 1) * perPage, userPage * perPage).map(function (item) {
      return '<div class="vpscs-sw-row"><div class="vpscs-sw-row-head"><strong>' + escapeHtml(item.user_remark || '未设置备注') + '</strong><span class="' + (item.online ? 'vpscs-sw-online' : 'vpscs-sw-offline') + '">' + (item.online ? '● 在线' : '● 离线') + '</span></div><div>' + escapeHtml(item.user_identifier) + '</div><div class="vpscs-sw-muted">IP：' + escapeHtml(item.ip_address || '-') + ' · 版本：' + escapeHtml(item.app_version || '-') + '</div><div class="vpscs-sw-muted">节点：' + escapeHtml(item.current_node || '未连接') + '</div><div class="vpscs-sw-muted">最后活跃：' + escapeHtml(dateText(item.last_seen)) + '</div></div>';
    }).join('') || '<div class="vpscs-sw-row vpscs-sw-muted">还没有软件用户记录</div>';
    var logRows = logs.slice((logPage - 1) * perPage, logPage * perPage).map(function (item) {
      return '<div class="vpscs-sw-row"><div class="vpscs-sw-row-head"><strong>' + escapeHtml(item.subscription_label || '未命名订阅') + '</strong><button class="vpscs-sw-btn is-danger" data-delete-log="' + escapeHtml(item.id) + '">删除</button></div><div class="vpscs-sw-url">' + escapeHtml(item.subscription_url) + '</div><div class="vpscs-sw-muted">用户：' + escapeHtml(item.user_identifier || '未登录') + ' · IP：' + escapeHtml(item.ip_address || '-') + '</div><div class="vpscs-sw-muted">首次记录：' + escapeHtml(dateText(item.first_seen)) + '</div></div>';
    }).join('') || '<div class="vpscs-sw-row vpscs-sw-muted">还没有第三方订阅记录</div>';
    page.querySelector('[data-users]').innerHTML = userRows;
    page.querySelector('[data-logs]').innerHTML = logRows;
    page.querySelector('[data-user-page]').textContent = userPage + ' / ' + userPages;
    page.querySelector('[data-log-page]').textContent = logPage + ' / ' + logPages;
  }
  async function loadTelemetry(quiet) {
    if (!quiet) setStatus('正在读取软件使用情况…');
    try { var data = await api('telemetry?include_logs=1'); users = data.users || []; logs = data.subscription_logs || []; renderTelemetry(); if (!quiet) setStatus(''); }
    catch (error) { if (!quiet) setStatus('❌ ' + error.message); }
  }
  function createPage() {
    page = document.createElement('main'); page.id = 'vpscs-software-page';
    page.innerHTML = '<div class="vpscs-sw-shell"><header class="vpscs-sw-head"><div><h1>软件管理</h1><p class="vpscs-sw-muted">管理客户端公告、版本更新、应急节点和使用记录。</p></div><button class="vpscs-sw-btn" data-refresh>刷新数据</button></header><div class="vpscs-sw-status" data-status></div><form data-form><div class="vpscs-sw-grid">' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>内置应急节点</h2><label class="vpscs-sw-check"><input name="emergency_nodes_enabled" type="checkbox">启用</label></div><label class="vpscs-sw-field">内置订阅链接<input name="emergency_subscription_url" type="url" placeholder="https://example.com/subscription"></label></section>' +
      '<section class="vpscs-sw-card"><div class="vpscs-sw-title"><h2>顶部滚动公告</h2><label class="vpscs-sw-check"><input name="notice_enabled" type="checkbox">启用</label></div><label class="vpscs-sw-field">公告内容<textarea name="notice" rows="5" maxlength="300"></textarea></label></section>' +
      '<section class="vpscs-sw-card"><div class="vpscs-sw-title"><h2>登录用户弹窗公告</h2><label class="vpscs-sw-check"><input name="popup_enabled" type="checkbox">启用</label></div><label class="vpscs-sw-field">弹窗内容<textarea name="popup_content" rows="5" maxlength="1000"></textarea></label><label class="vpscs-sw-field">每位用户最多显示次数<input name="popup_display_count" type="number" min="1" max="20" value="1"></label><input name="popup_version" type="hidden"><div class="vpscs-sw-actions"><button type="button" class="vpscs-sw-btn" data-publish-popup>重新发布弹窗</button></div></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>软件接口域名</h2></div><label class="vpscs-sw-field">只填写域名，不填写协议或路径<input name="api_domain" placeholder="www.example.com"></label></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>软件版本更新</h2><label class="vpscs-sw-check"><input name="update_enabled" type="checkbox">启用更新</label></div><div class="vpscs-sw-grid"><label class="vpscs-sw-field">最新版本号<input name="latest_version" placeholder="0.3.3"></label><label class="vpscs-sw-check"><input name="force_update" type="checkbox">强制更新</label><label class="vpscs-sw-field is-wide">Windows 安装包 HTTPS 地址<input name="windows_download_url" type="url"></label><label class="vpscs-sw-field is-wide">macOS 安装包 HTTPS 地址<input name="macos_download_url" type="url"></label><label class="vpscs-sw-field is-wide">更新说明<textarea name="update_notes" rows="5" maxlength="3000"></textarea></label></div></section>' +
      '<section class="vpscs-sw-card is-wide"><div class="vpscs-sw-title"><h2>客户端接口</h2></div><div class="vpscs-sw-code">GET ' + escapeHtml(location.origin + '/api/v1/guest/software/config') + '</div><div class="vpscs-sw-code" style="margin-top:10px">POST ' + escapeHtml(location.origin + '/api/v1/guest/software/telemetry') + '</div></section>' +
      '<div class="vpscs-sw-actions"><button type="submit" class="vpscs-sw-btn is-primary">保存全部软件配置</button></div></div></form>' +
      '<section class="vpscs-sw-card" style="margin-top:22px"><div class="vpscs-sw-title"><h2>软件使用情况</h2></div><div class="vpscs-sw-stats"><div class="vpscs-sw-stat">当前在线<strong data-online-count>0</strong></div><div class="vpscs-sw-stat">登录过的软件用户<strong data-user-count>0</strong></div><div class="vpscs-sw-stat">不重复订阅链接<strong data-log-count>0</strong></div></div><div class="vpscs-sw-lists"><div class="vpscs-sw-list"><h3>软件用户在线状态</h3><div data-users></div><div class="vpscs-sw-pager"><button class="vpscs-sw-btn" data-user-prev>上一页</button><span data-user-page>1 / 1</span><button class="vpscs-sw-btn" data-user-next>下一页</button></div></div><div class="vpscs-sw-list"><h3>第三方订阅日志</h3><div data-logs></div><div class="vpscs-sw-pager"><button class="vpscs-sw-btn" data-log-prev>上一页</button><span data-log-page>1 / 1</span><button class="vpscs-sw-btn" data-log-next>下一页</button></div></div></div></section></div>';
    document.body.appendChild(page); form = page.querySelector('[data-form]');
    form.addEventListener('submit', function (event) { event.preventDefault(); saveConfig(false); });
    page.addEventListener('click', async function (event) {
      var button = event.target.closest('button'); if (!button) return; event.preventDefault();
      if (button.matches('[data-refresh]')) { await Promise.all([loadConfig(), loadTelemetry()]); }
      if (button.matches('[data-publish-popup]')) saveConfig(true);
      if (button.matches('[data-user-prev]')) { userPage = Math.max(1, userPage - 1); renderTelemetry(); }
      if (button.matches('[data-user-next]')) { userPage++; renderTelemetry(); }
      if (button.matches('[data-log-prev]')) { logPage = Math.max(1, logPage - 1); renderTelemetry(); }
      if (button.matches('[data-log-next]')) { logPage++; renderTelemetry(); }
      if (button.matches('[data-delete-log]') && confirm('确定删除这条第三方订阅记录吗？')) { try { await api('subscription/delete', { method: 'POST', body: JSON.stringify({ id: button.dataset.deleteLog }) }); await loadTelemetry(); } catch (error) { setStatus('❌ ' + error.message); } }
    });
  }
  function installMenu() {
    if (document.querySelector('[data-vpscs-software-menu]')) return;
    var links = Array.from(document.querySelectorAll('#root a[href]'));
    var source = links.find(function (link) { return /用户管理/.test(link.textContent || ''); }) || links.find(function (link) { return /\/user(?:s)?(?:\?|$)/i.test(link.getAttribute('href') || ''); });
    if (!source) return;
    var link = source.cloneNode(false); link.href = '#/software-management'; link.dataset.vpscsSoftwareMenu = '1'; link.classList.add('vpscs-software-menu'); link.innerHTML = '<span aria-hidden="true">▦</span><span>软件管理</span>';
    source.insertAdjacentElement('afterend', link);
  }
  function syncRoute() {
    var active = /#\/software-management(?:\?|$)/.test(location.hash); page.classList.toggle('is-active', active);
    if (active) { loadConfig(); loadTelemetry(); clearInterval(timer); timer = setInterval(function () { loadTelemetry(true); }, 5000); }
    else { clearInterval(timer); timer = null; }
  }
  function boot() { createPage(); installMenu(); new MutationObserver(installMenu).observe(document.getElementById('root'), { childList: true, subtree: true }); addEventListener('hashchange', syncRoute); syncRoute(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true }); else boot();
})();
