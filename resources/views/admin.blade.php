<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $title }}</title>
  <script>
    // The upstream admin bundle defaults to light mode.  Migrate existing
    // browsers once, then keep the administrator's later manual choice.
    try {
      if (!localStorage.getItem('vpscs-dark-default-v2')) {
        localStorage.setItem('vite-ui-theme', 'dark');
        localStorage.setItem('vpscs-dark-default-v2', '1');
      }
    } catch (_) {}
    window.settings = {{ Illuminate\Support\Js::from([
      'base_url' => '/',
      'title' => $title,
      'version' => $version,
      'logo' => $logo,
      'secure_path' => $secure_path,
    ]) }};
    window.__xboardAdminBootErrors = [];
    window.addEventListener('error', function (event) {
      window.__xboardAdminBootErrors.push(event.message || 'Unknown resource error');
    }, true);
  </script>
  @php
    $manifestPath = public_path('assets/admin/manifest.json');
    $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;
    $entry = is_array($manifest) ? ($manifest['index.html'] ?? null) : null;
    $scripts = [];
    $styles = [];
    $locales = [];

    if (is_array($entry)) {
      $visited = [];
      $collectAssets = function ($chunkName) use (&$collectAssets, &$manifest, &$visited, &$scripts, &$styles) {
        if (isset($visited[$chunkName]) || !isset($manifest[$chunkName]) || !is_array($manifest[$chunkName])) {
          return;
        }

        $visited[$chunkName] = true;
        $chunk = $manifest[$chunkName];

        if (!empty($chunk['css']) && is_array($chunk['css'])) {
          foreach ($chunk['css'] as $cssFile) {
            $styles[$cssFile] = $cssFile;
          }
        }

        if (!empty($chunk['imports']) && is_array($chunk['imports'])) {
          foreach ($chunk['imports'] as $import) {
            $collectAssets($import);
          }
        }

        if (!empty($chunk['isEntry']) && !empty($chunk['file'])) {
          $scripts[$chunk['file']] = $chunk['file'];
        }
      };

      $collectAssets('index.html');
    }

    foreach (glob(public_path('assets/admin/locales/*.js')) ?: [] as $localeFile) {
      $locales[] = 'locales/' . basename($localeFile);
    }
    sort($locales);
  @endphp

  @if($entry && count($scripts) > 0)
    @foreach($styles as $css)
      <link rel="stylesheet" crossorigin href="/assets/admin/{{ $css }}" />
    @endforeach
    @foreach($locales as $locale)
      <script src="/assets/admin/{{ $locale }}"></script>
    @endforeach
    @foreach($scripts as $js)
      <script type="module" crossorigin src="/assets/admin/{{ $js }}"></script>
    @endforeach
  @else
    {{-- Fallback: hardcoded paths for backward compatibility --}}
    <script type="module" crossorigin src="/assets/admin/assets/index.js"></script>
    <link rel="stylesheet" crossorigin href="/assets/admin/assets/index.css" />
    <link rel="stylesheet" crossorigin href="/assets/admin/assets/vendor.css">
    <script src="/assets/admin/locales/en-US.js"></script>
    <script src="/assets/admin/locales/zh-CN.js"></script>
    <script src="/assets/admin/locales/ko-KR.js"></script>
  @endif
  <style id="vpscs-admin-dark-theme">
    html.dark {
      color-scheme: dark;
      --el-bg-color: #0d1422;
      --el-bg-color-page: #080d17;
      --el-bg-color-overlay: #111a2b;
      --el-fill-color: #162033;
      --el-fill-color-light: #18243a;
      --el-fill-color-lighter: #1b2942;
      --el-fill-color-blank: #0f1828;
      --el-text-color-primary: #f1f5f9;
      --el-text-color-regular: #d3dbea;
      --el-text-color-secondary: #9caac0;
      --el-text-color-placeholder: #718096;
      --el-border-color: #2b3a52;
      --el-border-color-light: #25334a;
      --el-border-color-lighter: #202d42;
      --el-border-color-extra-light: #1b2739;
      --el-mask-color: rgba(2, 6, 15, .78);
      --el-color-primary: #7c5cff;
      --el-color-primary-light-3: #927cff;
      --el-color-primary-light-5: #a99aff;
      --el-color-primary-light-8: #282343;
      --el-color-primary-light-9: #1a1930;
    }
    html.dark, html.dark body, html.dark #root, html.dark .el-container, html.dark .el-main {
      background: #080d17 !important;
      color: #e8edf6 !important;
    }
    html.dark .el-aside, html.dark .el-header, html.dark .el-menu, html.dark .el-menu--popup, html.dark .el-card,
    html.dark .el-dialog, html.dark .el-dialog__body, html.dark .el-drawer, html.dark .el-drawer__body,
    html.dark .el-popover.el-popper, html.dark .el-dropdown__popper, html.dark .el-select__popper,
    html.dark .el-picker-panel, html.dark .el-message-box {
      background: #0f1828 !important;
      color: #e8edf6 !important;
      border-color: #293750 !important;
    }
    html.dark .el-card, html.dark .el-table, html.dark .el-table tr, html.dark .el-table th.el-table__cell,
    html.dark .el-table td.el-table__cell, html.dark .el-table__expanded-cell,
    html.dark .el-descriptions__body, html.dark .el-descriptions__table, html.dark .el-pagination {
      background: #0f1828 !important;
      color: #dbe4f2 !important;
      border-color: #293750 !important;
    }
    html.dark .el-table th.el-table__cell,
    html.dark .el-table--enable-row-hover .el-table__body tr:hover > td.el-table__cell,
    html.dark .el-menu-item:hover, html.dark .el-sub-menu__title:hover {
      background: #18243a !important;
    }
    html.dark .el-input__wrapper, html.dark .el-textarea__inner, html.dark .el-select__wrapper,
    html.dark .el-input-number, html.dark .el-date-editor, html.dark .el-upload-dragger {
      background: #101a2b !important;
      color: #eef2f8 !important;
      box-shadow: 0 0 0 1px #2b3a52 inset !important;
      border-color: #2b3a52 !important;
    }
    html.dark .el-input__inner, html.dark .el-textarea__inner, html.dark .el-select__selected-item,
    html.dark .el-form-item__label, html.dark .el-dialog__title, html.dark .el-drawer__title,
    html.dark .el-card__header, html.dark .el-menu-item, html.dark .el-sub-menu__title,
    html.dark h1, html.dark h2, html.dark h3, html.dark h4, html.dark h5, html.dark h6 { color: #eef2f8 !important; }
    html.dark .el-menu-item.is-active, html.dark a { color: #9f8cff !important; }
    html.dark .el-empty__description, html.dark .el-text, html.dark small, html.dark .text-gray-500 {
      color: #9caac0 !important;
    }
    html.dark .el-table::before, html.dark .el-table__inner-wrapper::before, html.dark .el-card,
    html.dark .el-dialog, html.dark .el-drawer { border-color: #293750 !important; }
    html.dark .el-tag,
    html.dark .el-tag.el-tag--info,
    html.dark .el-check-tag,
    html.dark .el-statistic,
    html.dark .el-button:not(.el-button--primary):not(.is-link) {
      --el-tag-bg-color: #18243a;
      --el-tag-border-color: #344662;
      --el-tag-text-color: #dce6f5;
      background: #18243a !important;
      color: #dce6f5 !important;
      border-color: #344662 !important;
    }
    html.dark .el-tag *, html.dark .el-statistic *,
    html.dark .el-button:not(.el-button--primary):not(.is-link) * {
      color: inherit !important;
    }
    html.dark .el-switch:not(.is-checked) .el-switch__core {
      background: #25334a !important;
      border-color: #3a4c68 !important;
    }

    /* The bundled admin hard-codes Tailwind's light shades for price chips and
     * status pills (bg-slate-100/80, bg-blue-100 ...).  Those classes have no
     * dark: variant, so they stay glaring white on a dark page.  Re-tone the
     * ones actually used by the plan/user tables. */
    html.dark #root [class~="bg-slate-100/80"],
    html.dark #root [class~="bg-slate-100"],
    html.dark #root .bg-gray-100,
    html.dark #root .bg-gray-50,
    html.dark #root .bg-white {
      background-color: rgba(148, 163, 184, .14) !important;
      color: #d7e1f0 !important;
      border-color: rgba(148, 163, 184, .24) !important;
    }
    html.dark #root [class~="bg-slate-100/80"]:hover,
    html.dark #root [class~="bg-slate-100"]:hover,
    html.dark #root .bg-gray-100:hover {
      background-color: rgba(148, 163, 184, .22) !important;
    }
    html.dark #root .text-slate-700, html.dark #root .text-slate-800,
    html.dark #root .text-gray-700, html.dark #root .text-gray-800 {
      color: #cbd5e1 !important;
    }
    html.dark #root .bg-blue-100,
    html.dark #root .bg-indigo-100 { background-color: rgba(99, 102, 241, .18) !important; border-color: rgba(99, 102, 241, .32) !important; }
    html.dark #root .text-blue-800, html.dark #root .text-indigo-800 { color: #c7d2fe !important; }
    html.dark #root .bg-green-100,
    html.dark #root .bg-emerald-100 { background-color: rgba(16, 185, 129, .18) !important; border-color: rgba(16, 185, 129, .32) !important; }
    html.dark #root .text-green-800, html.dark #root .text-emerald-800 { color: #a7f3d0 !important; }
    html.dark #root .bg-red-100 { background-color: rgba(239, 68, 68, .18) !important; border-color: rgba(239, 68, 68, .32) !important; }
    html.dark #root .text-red-800 { color: #fecaca !important; }
    html.dark #root .bg-yellow-100,
    html.dark #root .bg-orange-100 { background-color: rgba(245, 158, 11, .18) !important; border-color: rgba(245, 158, 11, .32) !important; }
    html.dark #root .text-yellow-800, html.dark #root .text-orange-800 { color: #fde68a !important; }
    html.dark #root .bg-purple-100 { background-color: rgba(168, 85, 247, .18) !important; border-color: rgba(168, 85, 247, .32) !important; }
    html.dark #root .text-purple-800 { color: #e9d5ff !important; }

    /* The upstream dark palette maps primary to near-white, which makes
     * switches and primary buttons look like pure white blocks.  Use the
     * brand indigo instead so controls read as controls, not highlights. */
    html.dark {
      --primary: 221 83% 58%;
      --primary-foreground: 210 40% 98%;
      --ring: 221 83% 58%;
    }
  </style>
  @php
    // 用资源指纹代替手写版本号，避免 Cloudflare 边缘节点继续提供旧文件。
    $swSignature = '';
    foreach (['software-admin.js', 'software-admin.css'] as $swFile) {
        $swPath = public_path('assets/' . $swFile);
        $swSignature .= @filemtime($swPath) . '|' . @filesize($swPath) . '|';
    }
    $swVersion = substr(md5($swSignature), 0, 10);
  @endphp
  <link rel="stylesheet" href="/assets/software-admin.css?v={{ $swVersion }}">
</head>

<body>
  <div id="root"></div>
  <script>
    window.setTimeout(function () {
      var root = document.getElementById('root');
      if (!root || root.childElementCount > 0 || root.textContent.trim()) return;

      var errors = window.__xboardAdminBootErrors || [];
      root.innerHTML = '<main style="max-width:720px;margin:64px auto;padding:24px;font-family:system-ui;color:#b42318;background:#fff;border:1px solid #f1c0bc;border-radius:12px">' +
        '<h1 style="font-size:20px;margin:0 0 12px">管理员后台加载失败</h1>' +
        '<p style="color:#555">请先按 Ctrl+F5 强制刷新。若仍然出现此提示，请检查后台静态资源是否完整。</p>' +
        (errors.length ? '<pre style="white-space:pre-wrap;color:#7a271a">' + errors.join('\n') + '</pre>' : '') +
        '</main>';
    }, 8000);

    // Compatibility enhancement for the bundled admin UI: make the implicit
    // pricing model explicit and prevent periodic/one-time prices being saved
    // together. The backend still uses the established prices JSON structure.
    (function () {
      var periodicLabels = ['月付', '季付', '半年付', '年付', '两年付', '三年付'];
      var oneTimeLabels = ['一次性', '流量包', '一次性套餐'];

      function fieldByLabel(root, labels) {
        var nodes = root.querySelectorAll('label');
        for (var i = 0; i < nodes.length; i++) {
          var text = (nodes[i].textContent || '').trim();
          if (labels.some(function (label) { return text === label; })) {
            return nodes[i].closest('[data-slot="form-item"]') || nodes[i].closest('.space-y-1\\.5') || nodes[i].parentElement;
          }
        }
        return null;
      }

      function updateInput(field, value) {
        if (!field) return;
        var input = field.querySelector('input');
        if (!input) return;
        var setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
        setter.call(input, value);
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }

      function enhancePlanDialog(dialog) {
        if (dialog.dataset.vpscsPlanTypeReady === '1') return;
        var periodicFields = periodicLabels.map(function (label) { return fieldByLabel(dialog, [label]); }).filter(Boolean);
        var oneTimeField = fieldByLabel(dialog, oneTimeLabels);
        if (!periodicFields.length || !oneTimeField) return;

        dialog.dataset.vpscsPlanTypeReady = '1';
        var box = document.createElement('div');
        box.className = 'vpscs-plan-type';
        box.innerHTML = '<label>套餐类型</label><select><option value="periodic">周期套餐（月付、季付、年付等）</option><option value="onetime">一次性套餐</option></select><small>两种类型不会再同时保存；切换类型会清空另一类型的价格。</small>';
        var anchor = periodicFields[0].parentElement;
        anchor.parentElement.insertBefore(box, anchor);
        var select = box.querySelector('select');

        function apply(type, clearOther) {
          periodicFields.forEach(function (field) {
            field.style.display = type === 'periodic' ? '' : 'none';
            if (clearOther && type === 'onetime') updateInput(field, '');
          });
          oneTimeField.style.display = type === 'onetime' ? '' : 'none';
          if (clearOther && type === 'periodic') updateInput(oneTimeField, '');
        }
        var hasPeriodic = periodicFields.some(function (field) { var input = field.querySelector('input'); return input && Number(input.value) > 0; });
        var oneInput = oneTimeField.querySelector('input');
        select.value = oneInput && Number(oneInput.value) > 0 && !hasPeriodic ? 'onetime' : 'periodic';
        select.addEventListener('change', function () { apply(select.value, true); });
        apply(select.value, false);
      }

      new MutationObserver(function () {
        document.querySelectorAll('[role="dialog"]').forEach(enhancePlanDialog);
      }).observe(document.body, { childList: true, subtree: true });
    })();

    (function () {
      var nativeFetch = window.fetch.bind(window);
      var profiles = [];
      var adminUsers = [];
      // The admin bundle talks to the API through axios (fetch adapter in some
      // builds) and the browser's XHR in others.  Both paths feed this helper,
      // otherwise the user table never receives subscription_profiles and no
      // per-row picker can be built.
      function applyUserList(payload) {
        if (!payload) return;
        var data = payload.data;
        var list = Array.isArray(data) ? data : (data && Array.isArray(data.data) ? data.data : null);
        if (!list || !list.length) return;
        adminUsers = list;
        paintRowProfiles();
        refreshAdminEnhancements();
      }
      function applyUserListText(text) {
        try { applyUserList(JSON.parse(text)); } catch (_) {}
      }
      window.fetch = function (input, init) {
        var url = typeof input === 'string' ? input : input.url;
        var selectedId = activeSubscriptionId();
        var targetsSubscription = selectedId && /\/api\/v2\/admin\/(user\/(update|resetSecret|drop)|order\/|stat\/user)/i.test(url);
        if (targetsSubscription && typeof input === 'string') {
          try { var parsed = new URL(input, location.origin); ['id', 'user_id'].forEach(function (key) { if (parsed.searchParams.has(key)) parsed.searchParams.set(key, selectedId); }); input = parsed.pathname + parsed.search; url = input; } catch (_) {}
        }
        if (targetsSubscription && init && typeof init.body === 'string') {
          try {
            var body = JSON.parse(init.body);
            if ('id' in body) body.id = Number(selectedId);
            if ('user_id' in body) body.user_id = Number(selectedId);
            if (Array.isArray(body.user_ids)) body.user_ids = [Number(selectedId)];
            init = Object.assign({}, init, { body: JSON.stringify(body) });
          } catch (_) {}
        }
        return nativeFetch(input, init).then(function (response) {
          if (/user\/getUserInfoById/i.test(url)) response.clone().json().then(function (payload) {
            profiles = payload.data && payload.data.subscription_profiles || [];
            refreshAdminEnhancements();
          }).catch(function () {});
          if (/user\/fetch/i.test(url)) { response.clone().text().then(applyUserListText).catch(function () {}); }
          else if (/\/user\/(update|destroy|resetSecret)\b|\/order\//i.test(url)) { scheduleUserListRefresh(); }
          return response;
        });
      };
      // The current React admin uses Axios/XMLHttpRequest, so mirror the same
      // capture and selected-subscription routing for XHR requests.
      var nativeOpen = XMLHttpRequest.prototype.open;
      var nativeSend = XMLHttpRequest.prototype.send;
      XMLHttpRequest.prototype.open = function (method, url) {
        var selectedId = activeSubscriptionId();
        var nextUrl = String(url);
        if (selectedId && /\/(user\/(update|resetSecret|destroy)|order\/|stat\/getStatUser)/i.test(nextUrl)) {
          try {
            var parsed = new URL(nextUrl, location.origin);
            ['id', 'user_id'].forEach(function (key) { if (parsed.searchParams.has(key)) parsed.searchParams.set(key, selectedId); });
            nextUrl = parsed.href;
          } catch (_) {}
        }
        this.__vpscsUrl = nextUrl;
        this.addEventListener('load', function () {
          var url = this.__vpscsUrl || '';
          if (!/\/user\/fetch/i.test(url)) {
            if (/\/user\/(update|destroy|resetSecret)\b|\/user\/resetSecret|\/order\//i.test(url)) scheduleUserListRefresh();
            return;
          }
          // axios may request responseType=json; reading responseText then throws
          // (InvalidStateError), which used to leave the table without data.
          try {
            if (this.responseType === 'json' && this.response) { applyUserList(this.response); return; }
            applyUserListText(this.responseText);
          } catch (_) {}
        });
        return nativeOpen.apply(this, [method, nextUrl].concat(Array.prototype.slice.call(arguments, 2)));
      };
      XMLHttpRequest.prototype.send = function (body) {
        var selectedId = activeSubscriptionId();
        if (selectedId && /\/(user\/(update|resetSecret|destroy)|order\/|stat\/getStatUser)/i.test(this.__vpscsUrl || '') && typeof body === 'string') {
          try {
            var payload = JSON.parse(body);
            if ('id' in payload) payload.id = Number(selectedId);
            if ('user_id' in payload) payload.user_id = Number(selectedId);
            if (Array.isArray(payload.user_ids)) payload.user_ids = [Number(selectedId)];
            body = JSON.stringify(payload);
          } catch (_) {}
        }
        return nativeSend.call(this, body);
      };
      // 只有「编辑用户」弹窗里的订阅面板打开时，才允许把请求里的 id
      // 换成选中的订阅 id。否则在列表里直接点删除，会被上一轮残留的
      // 订阅 id 劫持，删错账号。
      function activeSubscriptionId() {
        if (!document.querySelector('.vpscs-admin-subscriptions')) return '';
        return sessionStorage.getItem('vpscs_admin_subscription_id') || '';
      }
      // 备注号：后端按 id 升序算好的 seq，用户端「我的订阅」用同一个数字。
      function profileNo(item, index) {
        var no = Number(item && item.seq);
        return isFinite(no) && no > 0 ? no : index + 1;
      }
      function profileDate(item) {
        return item && item.expired_at ? new Date(Number(item.expired_at) * 1000).toLocaleDateString() : '长期';
      }
      function profileLabel(item, index) {
        return '订阅' + profileNo(item, index) + ' · ' + (item.name || '订阅') + ' · ' + profileDate(item);
      }
      // 行内订阅清单用的短格式：日期统一 YYYY-MM-DD，流量用 GB/MB 自适应。
      function profileDay(item) {
        if (!item || !item.expired_at) return '长期有效';
        var date = new Date(Number(item.expired_at) * 1000);
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
      }
      function profileTraffic(item) {
        function size(bytes) {
          var gb = Number(bytes || 0) / 1073741824;
          return gb >= 1 ? gb.toFixed(2) + ' GB' : (gb * 1024).toFixed(0) + ' MB';
        }
        var used = Number((item && item.u) || 0) + Number((item && item.d) || 0);
        return size(used) + ' / ' + size(item && item.transfer_enable);
      }
      // 过期订阅不再出现在任何选择框里（后端已过滤，这里兜底防止旧缓存）。
      function liveProfiles(items) {
        var now = Date.now();
        return (items || []).filter(function (item) {
          var expiredAt = Number(item && item.expired_at);
          return !(expiredAt > 0 && expiredAt * 1000 <= now);
        });
      }
      function subscriptionSignature(items) {
        return (items || []).map(function (item) { return [item.id, profileNo(item, 0), item.name, item.expired_at, item.transfer_enable, item.u, item.d].join(':'); }).join(',');
      }

      // ---- 列表行上的「订阅 / 到期时间」 ----
      // 后台列表一行是一个账号，账号下可能有多条订阅。这两列统一显示
      // 「当前订阅」（订阅1，也就是排在最前面、仍然有效的那一条）；
      // 把这一条删掉以后，位置会自动落到下一条，不会停在已经删掉的订阅上。
      function setCellText(cell, text) {
        if (!cell) return;
        var walker = document.createTreeWalker(cell, NodeFilter.SHOW_TEXT, null);
        var node;
        var target = null;
        while ((node = walker.nextNode())) {
          if (/\S/.test(node.nodeValue)) target = node;
        }
        if (target) { target.nodeValue = text; return; }
        if (cell.lastElementChild) cell.lastElementChild.appendChild(document.createTextNode(text));
        else cell.textContent = text;
      }
      function userTableElement() {
        var tables = document.querySelectorAll('#root table');
        for (var i = 0; i < tables.length; i += 1) {
          var head = tables[i].querySelector('thead');
          if (!head) continue;
          var text = head.textContent.replace(/\s+/g, '');
          if (/订阅/.test(text) && /邮箱/.test(text)) return tables[i];
        }
        return null;
      }
      function rowEmail(row) {
        var cells = row.children;
        for (var i = 0; i < cells.length; i += 1) {
          var match = (cells[i].textContent || '').match(/[\w.+-]+@[\w-]+(?:\.[\w-]+)+/);
          if (match) return match[0];
        }
        return '';
      }
      // 当前订阅 = subscription_profiles 里的第一条（后端已按 id 升序、过滤过期）。
      function currentProfileFor(user) {
        var list = (user && user.subscription_profiles) || [];
        for (var i = 0; i < list.length; i += 1) {
          if (list[i] && list[i].id) return list[i];
        }
        return null;
      }
      // 只改文字、保留原来的 <div>/<span> 结构，避免把 React 的节点拆坏。
      function paintCellText(cell, text, title) {
        if (!cell) return;
        if (cell.dataset.vpscsPainted && cell.textContent.trim() !== cell.dataset.vpscsPainted) {
          // React 重新渲染过这一格，把它的新文字当作「原始值」，然后重画。
          cell.dataset.vpscsNativeText = cell.textContent.trim();
          delete cell.dataset.vpscsPainted;
        }
        if (cell.dataset.vpscsNativeText === undefined) cell.dataset.vpscsNativeText = cell.textContent.trim();
        if (cell.dataset.vpscsPainted === text) return;
        setCellText(cell, text);
        cell.dataset.vpscsPainted = text;
        if (title) cell.title = title;
      }
      function restoreCellText(cell) {
        if (!cell || cell.dataset.vpscsNativeText === undefined) return;
        if (cell.dataset.vpscsPainted) setCellText(cell, cell.dataset.vpscsNativeText);
        delete cell.dataset.vpscsNativeText;
        delete cell.dataset.vpscsPainted;
        cell.removeAttribute('title');
      }
      // 原来的日期是「已过期」的红色样式，换成还有效的日期就得把颜色收回来。
      function neutralizeExpiry(cell) {
        var box = cell && cell.querySelector('[class*="border-destructive"]');
        if (!box || box.dataset.vpscsColor === '1') return;
        box.dataset.vpscsColor = '1';
        box.style.color = 'hsl(var(--muted-foreground))';
        box.style.borderColor = 'hsl(var(--border))';
        box.style.backgroundColor = 'transparent';
      }
      function restoreExpiryColor(cell) {
        var box = cell && cell.querySelector('[class*="border-destructive"]');
        if (!box || box.dataset.vpscsColor !== '1') return;
        box.style.color = '';
        box.style.borderColor = '';
        box.style.backgroundColor = '';
        delete box.dataset.vpscsColor;
      }
      // 流量显示成后台那种 1.38 MB / 1 GB，避免 1073741824 这种原始字节。
      function fmtBytes(bytes) {
        var value = Number(bytes || 0);
        if (!(value > 0)) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        var index = 0;
        while (value >= 1024 && index < units.length - 1) { value = value / 1024; index += 1; }
        var text = index === 0 ? String(Math.round(value)) : value.toFixed(2).replace(/\.?0+$/, '');
        return text + ' ' + units[index];
      }
      function setNodeText(node, text) {
        if (node && node.textContent !== text) node.textContent = text;
      }
      // 已用流量 / 总流量也跟着当前订阅走，不然「订阅」写 A、流量却是账号本身的。
      function paintTraffic(row, profile, usedIndex, totalIndex) {
        var usedCell = usedIndex >= 0 ? row.children[usedIndex] : null;
        var totalCell = totalIndex >= 0 ? row.children[totalIndex] : null;
        var usedNode = usedCell && usedCell.querySelector('span');
        var percentNode = usedCell && usedCell.querySelectorAll('span')[1];
        var bar = usedCell && usedCell.querySelector('[style*="width"]');
        var totalNode = totalCell && totalCell.querySelector('div');

        if (!profile) {
          if (usedCell && usedCell.dataset.vpscsNativeUsed !== undefined) {
            setNodeText(usedNode, usedCell.dataset.vpscsNativeUsed);
            setNodeText(percentNode, usedCell.dataset.vpscsNativePercent);
            if (bar && usedCell.dataset.vpscsNativeBar !== undefined) bar.style.width = usedCell.dataset.vpscsNativeBar;
          }
          if (totalCell && totalCell.dataset.vpscsNativeTotal !== undefined) setNodeText(totalNode, totalCell.dataset.vpscsNativeTotal);
          return;
        }

        var used = Number(profile.u || 0) + Number(profile.d || 0);
        var total = Number(profile.transfer_enable || 0);
        var percent = total > 0 ? Math.min(100, (used / total) * 100) : 0;
        if (usedCell) {
          if (usedCell.dataset.vpscsNativeUsed === undefined && usedNode) {
            usedCell.dataset.vpscsNativeUsed = usedNode.textContent;
            usedCell.dataset.vpscsNativePercent = percentNode ? percentNode.textContent : '';
            usedCell.dataset.vpscsNativeBar = bar ? bar.style.width : '';
          }
          setNodeText(usedNode, fmtBytes(used));
          setNodeText(percentNode, percent.toFixed(1) + '%');
          if (bar) {
            var width = percent + '%';
            if (bar.style.width !== width) bar.style.width = width;
          }
        }
        if (totalCell && totalNode) {
          if (totalCell.dataset.vpscsNativeTotal === undefined) totalCell.dataset.vpscsNativeTotal = totalNode.textContent;
          setNodeText(totalNode, fmtBytes(total));
        }
      }
      // 邮箱前面挂一个「N 条」小标签：不用点开编辑就知道这个账号有几条订阅。
      function paintSubCount(cell, count) {
        var holder = cell && cell.querySelector('div');
        if (!holder) return;
        var text = count + ' 条';
        if (holder.dataset.vpscsCount !== text) holder.dataset.vpscsCount = text;
        var tip = '该账号名下共 ' + count + ' 条有效订阅';
        if (holder.title !== tip) holder.title = tip;
      }
      function paintRowProfiles() {
        var table = userTableElement();
        if (!table) return;
        var heads = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) { return th.textContent.replace(/\s+/g, ''); });
        var nameIndex = heads.indexOf('订阅');
        var expiryIndex = heads.findIndex(function (head) { return /到期时间|接近时间|过期时间/.test(head); });
        var usedIndex = heads.findIndex(function (head) { return /已用流量/.test(head); });
        var totalIndex = heads.findIndex(function (head) { return /总流量|可用流量/.test(head); });
        var emailIndex = heads.indexOf('邮箱');
        if (nameIndex < 0 && expiryIndex < 0) return;
        Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function (row) {
          var email = rowEmail(row);
          var user = email && adminUsers.filter(function (item) { return item.email === email; })[0];
          var profile = currentProfileFor(user);
          var nameCell = nameIndex >= 0 ? row.children[nameIndex] : null;
          var expiryCell = expiryIndex >= 0 ? row.children[expiryIndex] : null;
          var emailCell = emailIndex >= 0 ? row.children[emailIndex] : null;
          paintTraffic(row, profile, usedIndex, totalIndex);
          if (user && emailCell) {
            var count = Array.isArray(user.subscription_profiles) ? user.subscription_profiles.length : 0;
            paintSubCount(emailCell, count);
          }
          if (!profile) {
            restoreCellText(nameCell);
            restoreCellText(expiryCell);
            restoreExpiryColor(expiryCell);
            return;
          }
          var name = profile.name || (profile.plan && profile.plan.name) || '订阅';
          var day = profileDay(profile);
          var hint = '当前显示订阅' + profileNo(profile, 0) + '（ID ' + profile.id + '）的';
          paintCellText(nameCell, name, hint + '套餐');
          paintCellText(expiryCell, day, hint + '到期时间');
          if (profile.expired_at) restoreExpiryColor(expiryCell);
          else neutralizeExpiry(expiryCell);
        });
      }

      // ---- 编辑弹窗里的订阅面板 ----
      // 列表行里不再放订阅选择器（点「编辑」就能管理全部订阅），
      // 这里除了切换订阅，还能直接改流量、改到期、删除该订阅。
      function subscriptionRequest(path, body) {
        var secure = (window.settings && window.settings.secure_path) || '';
        return nativeFetch('/api/v2/' + secure + path, {
          method: 'POST',
          headers: { Authorization: authHeader(adminToken()), 'Content-Type': 'application/json', 'Content-Language': 'zh-CN' },
          credentials: 'same-origin',
          body: JSON.stringify(body || {}),
        }).then(function (response) {
          return response.json().catch(function () { return {}; }).then(function (payload) {
            if (!response.ok) throw new Error((payload && payload.message) || ('请求失败（' + response.status + '）'));
            return payload && Object.prototype.hasOwnProperty.call(payload, 'data') ? payload.data : payload;
          });
        });
      }
      function gbToBytes(gb) { return Math.max(0, Math.round(Number(gb || 0) * 1073741824)); }
      function bytesToGb(bytes) { return Number(bytes || 0) / 1073741824; }
      function dateInputValue(timestamp) {
        if (!timestamp) return '';
        var date = new Date(Number(timestamp) * 1000);
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
      }
      // 不用 window.confirm：它会卡住整个页面（React 也停住），抽屉里样式也难看。
      function confirmAction(message) {
        return new Promise(function (resolve) {
          var mask = document.createElement('div');
          mask.className = 'vpscs-confirm-mask';
          mask.innerHTML = '<div class="vpscs-confirm"><p></p><div class="vpscs-confirm-actions">'
            + '<button type="button" data-answer="0">取消</button>'
            + '<button type="button" class="is-danger" data-answer="1">删除</button>'
            + '</div></div>';
          mask.querySelector('p').textContent = message;
          document.body.appendChild(mask);
          var done = function (answer) {
            mask.remove();
            resolve(answer);
          };
          mask.addEventListener('click', function (event) {
            var button = event.target.closest('button[data-answer]');
            if (button) { done(button.getAttribute('data-answer') === '1'); return; }
            if (event.target === mask) done(false);
          });
        });
      }
      function subscriptionStatus(box, message, kind) {
        var node = box && box.querySelector('.vpscs-sub-status');
        if (!node) return;
        node.textContent = message || '';
        node.className = 'vpscs-sub-status' + (kind ? ' is-' + kind : '');
      }
      function repaintSubscriptionPanel(message, kind) {
        var box = document.querySelector('.vpscs-admin-subscriptions');
        if (!box) return;
        box.dataset.signature = '';
        renderSubscriptionPicker();
        paintRowProfiles();
        var fresh = document.querySelector('.vpscs-admin-subscriptions');
        if (fresh && message) subscriptionStatus(fresh, message, kind);
      }
      // 切换当前订阅后，把这条挪到 subscription_profiles 最前面并重打
      // is_current 标记：列表行只读第一条，挪完就等于「列表换成这一条」。
      function markCurrentProfile(id) {
        var key = String(id);
        function promote(list) {
          if (!Array.isArray(list)) return list;
          var hit = null;
          var rest = [];
          list.forEach(function (profile) {
            if (!hit && profile && String(profile.id) === key) { hit = profile; return; }
            rest.push(profile);
          });
          if (!hit) return list;
          return [hit].concat(rest);
        }
        adminUsers.forEach(function (user) {
          if (!Array.isArray(user.subscription_profiles)) return;
          if (!user.subscription_profiles.some(function (profile) { return profile && String(profile.id) === key; })) return;
          user.subscription_profiles = promote(user.subscription_profiles).map(function (profile) {
            profile.is_current = String(profile.id) === key;
            return profile;
          });
        });
        profiles = promote(profiles).map(function (profile) {
          profile.is_current = String(profile.id) === key;
          return profile;
        });
        var target = document.querySelector('.vpscs-admin-subscriptions');
        if (target) target.dataset.index = '0';
      }
      function saveSubscriptionTraffic(box, item, gb) {
        subscriptionStatus(box, '正在保存流量…');
        subscriptionRequest('/user/subscription/update', { id: item.id, transfer_enable: gb })
          .then(function (data) {
            item.transfer_enable = data && data.transfer_enable != null ? data.transfer_enable : gbToBytes(gb);
            if (data && data.u != null) item.u = data.u;
            if (data && data.d != null) item.d = data.d;
            repaintSubscriptionPanel('流量已改为 ' + bytesToGb(item.transfer_enable).toFixed(2) + ' GB', 'ok');
            scheduleUserListRefresh();
          })
          .catch(function (error) { subscriptionStatus(box, '保存失败：' + error.message, 'error'); });
      }
      function saveSubscriptionExpire(box, item, timestamp) {
        subscriptionStatus(box, '正在保存到期时间…');
        subscriptionRequest('/user/subscription/update', { id: item.id, expired_at: timestamp })
          .then(function (data) {
            item.expired_at = data && Object.prototype.hasOwnProperty.call(data, 'expired_at') ? data.expired_at : timestamp;
            repaintSubscriptionPanel(timestamp ? '到期时间已改为 ' + dateInputValue(timestamp) : '已设为长期有效', 'ok');
            scheduleUserListRefresh();
          })
          .catch(function (error) { subscriptionStatus(box, '保存失败：' + error.message, 'error'); });
      }
      function paintSubscriptionDetail(box, index) {
        var item = profiles[index] || {};
        // 「当前订阅」= 列表那一行正在显示的那条（后端已把它排在第一位）。
        var isCurrent = item.is_current === true || index === 0;
        sessionStorage.setItem('vpscs_admin_subscription_id', item.id || '');
        var detail = box.querySelector('.vpscs-admin-subscription-detail');
        if (!detail) return;
        detail.innerHTML = '';

        var head = document.createElement('div');
        head.className = 'vpscs-sub-line is-strong';
        head.textContent = '当前操作目标：订阅' + profileNo(item, index) + '（' + (item.name || '订阅') + '，ID ' + item.id + '）'
          + (isCurrent ? ' · 列表显示中' : '');
        detail.appendChild(head);

        var plan = document.createElement('div');
        plan.className = 'vpscs-sub-line';
        plan.textContent = '套餐：' + ((item.plan && item.plan.name) || item.name || '-');
        detail.appendChild(plan);

        var traffic = document.createElement('div');
        traffic.className = 'vpscs-sub-line vpscs-sub-edit';
        traffic.innerHTML = '<span class="vpscs-sub-key">流量</span>'
          + '<input class="vpscs-sub-amount" type="number" min="0" step="1" value="' + bytesToGb(item.transfer_enable).toFixed(2) + '"><span class="vpscs-sub-unit">GB</span>'
          + '<button type="button" data-act="traffic-minus">−10G</button>'
          + '<button type="button" data-act="traffic-plus">+10G</button>'
          + '<button type="button" data-act="traffic-plus100">+100G</button>'
          + '<button type="button" class="is-primary" data-act="traffic-save">保存</button>';
        detail.appendChild(traffic);

        var used = document.createElement('div');
        used.className = 'vpscs-sub-line is-muted';
        used.textContent = '已用 ' + bytesToGb(Number(item.u || 0) + Number(item.d || 0)).toFixed(2) + ' GB（改总量不影响已用）';
        detail.appendChild(used);

        var expire = document.createElement('div');
        expire.className = 'vpscs-sub-line vpscs-sub-edit';
        expire.innerHTML = '<span class="vpscs-sub-key">到期</span>'
          + '<input class="vpscs-sub-date" type="date" value="' + dateInputValue(item.expired_at) + '">'
          + '<button type="button" class="is-primary" data-act="expire-save">保存</button>'
          + '<button type="button" data-act="expire-clear">设为长期</button>';
        detail.appendChild(expire);

        var url = document.createElement('input');
        url.className = 'vpscs-sub-url';
        url.readOnly = true;
        url.value = item.subscribe_url || '';
        detail.appendChild(url);

        var actions = document.createElement('div');
        actions.className = 'vpscs-sub-line vpscs-sub-edit';
        // 只有一条订阅时切换没有意义，直接不给按钮。
        var switchButton = profiles.length > 1
          ? '<button type="button" data-act="switch"' + (isCurrent ? ' disabled title="这条已经是当前显示的订阅"' : ' class="is-primary" title="切换后列表的订阅 / 流量 / 到期时间都跟着这一条显示"') + '>'
            + (isCurrent ? '当前订阅' : '切换订阅') + '</button>'
          : '';
        actions.innerHTML = switchButton + '<button type="button" class="is-danger" data-act="delete">删除此订阅</button>';
        detail.appendChild(actions);

        var status = document.createElement('div');
        status.className = 'vpscs-sub-status';
        detail.appendChild(status);

        // 只绑一次：切下拉只是重画详情（detail 节点不重建），
        // 重复绑会把旧监听器留在里面，点「切换/删除」时拿着上一条订阅发请求。
        if (detail.dataset.vpscsBound === '1') return;
        detail.dataset.vpscsBound = '1';
        detail.addEventListener('click', function (event) {
          var button = event.target.closest('button[data-act]');
          if (button) event.stopPropagation();
          if (!button || button.disabled) return;
          // 操作目标永远取「当前选中的那一条」，不用渲染时闭包里的旧对象。
          var subject = profiles[Math.min(Math.max(0, Number(box.dataset.index || 0)), Math.max(0, profiles.length - 1))] || {};
          var act = button.getAttribute('data-act');
          var amount = detail.querySelector('.vpscs-sub-amount');
          var dateField = detail.querySelector('.vpscs-sub-date');
          if (act === 'traffic-save') {
            saveSubscriptionTraffic(box, subject, Math.max(0, Number(amount.value || 0)));
            return;
          }
          if (act === 'traffic-minus' || act === 'traffic-plus' || act === 'traffic-plus100') {
            var delta = act === 'traffic-minus' ? -10 : (act === 'traffic-plus' ? 10 : 100);
            saveSubscriptionTraffic(box, subject, Math.max(0, bytesToGb(subject.transfer_enable) + delta));
            return;
          }
          if (act === 'expire-save') {
            if (!dateField.value) { subscriptionStatus(box, '先选日期，或用「设为长期」', 'error'); return; }
            saveSubscriptionExpire(box, subject, Math.floor(new Date(dateField.value + 'T23:59:59').getTime() / 1000));
            return;
          }
          if (act === 'expire-clear') { saveSubscriptionExpire(box, subject, subject.expired_at ? 0 : null); return; }
          if (act === 'switch') {
            button.disabled = true;
            subscriptionStatus(box, '正在切换当前订阅…');
            subscriptionRequest('/user/subscription/switch', { id: subject.id })
              .then(function () {
                // 本地先把这条挪到最前：列表行的订阅 / 流量 / 到期立刻跟着变，
                // 600ms 后再拉一次接口，用后端算好的顺序对齐。
                markCurrentProfile(subject.id);
                repaintSubscriptionPanel('已切换到「' + (subject.name || '订阅') + '」，列表与流量已跟着这一条', 'ok');
                scheduleUserListRefresh();
              })
              .catch(function (error) {
                button.disabled = false;
                subscriptionStatus(box, '切换失败：' + error.message, 'error');
              });
            return;
          }
          if (act === 'delete') {
            confirmAction('删除「' + (subject.name || '订阅') + '」？订阅链接会立即失效，无法恢复。').then(function (ok) {
              if (!ok) return;
              button.disabled = true;
              subscriptionStatus(box, '正在删除…');
              subscriptionRequest('/user/subscription/destroy', { id: subject.id })
                .then(function () {
                  // 本地先把这条抹掉：列表行的「订阅 / 到期」立刻落到下一条，
                  // 600ms 后重新拉列表再对齐一次真实数据。
                  adminUsers.forEach(function (user) {
                    if (Array.isArray(user.subscription_profiles)) {
                      user.subscription_profiles = user.subscription_profiles.filter(function (profile) { return String(profile.id) !== String(subject.id); });
                    }
                  });
                  profiles = profiles.filter(function (profile) { return String(profile.id) !== String(subject.id); });
                  var panel = document.querySelector('.vpscs-admin-subscriptions');
                  if (panel) panel.dataset.index = '0';
                  paintRowProfiles();
                  repaintSubscriptionPanel('已删除该订阅', 'ok');
                  scheduleUserListRefresh();
                })
                .catch(function (error) {
                  button.disabled = false;
                  subscriptionStatus(box, '删除失败：' + error.message, 'error');
                });
            });
          }
        });
      }
      // 新版后台的「编辑」是右侧抽屉，没有 .el-dialog__body，得自己找滚动容器，
      // 否则面板被塞进抽屉根节点、顶到视口外看不见。
      function dialogScrollBody(dialog) {
        var body = dialog.querySelector('.el-dialog__body');
        if (body) return body;
        var nodes = Array.prototype.slice.call(dialog.querySelectorAll('*'));
        for (var i = 0; i < nodes.length; i += 1) {
          var node = nodes[i];
          var style = window.getComputedStyle(node);
          if ((style.overflowY === 'auto' || style.overflowY === 'scroll') && node.scrollHeight > node.clientHeight + 60) return node;
        }
        return dialog;
      }
      function renderSubscriptionPicker() {
        var dialog = Array.from(document.querySelectorAll('[role="dialog"]')).find(function (item) { return /用户|订阅/.test(item.textContent); });
        if (!dialog) return;
        var emailInput = Array.from(dialog.querySelectorAll('input')).find(function (input) { return /@/.test(input.value || ''); });
        var activeUser = emailInput && adminUsers.find(function (item) { return item.email === emailInput.value; });
        if (activeUser && Array.isArray(activeUser.subscription_profiles)) profiles = liveProfiles(activeUser.subscription_profiles);
        else profiles = liveProfiles(profiles);
        if (!profiles.length) {
          var stale = dialog.querySelector('.vpscs-admin-subscriptions');
          if (stale) stale.remove();
          return;
        }
        var box = dialog.querySelector('.vpscs-admin-subscriptions');
        var signature = subscriptionSignature(profiles);
        var index = box ? Math.min(Math.max(0, Number(box.dataset.index || 0)), profiles.length - 1) : 0;
        if (box && box.dataset.signature === signature && box.dataset.index === String(index)) return;
        if (!box) {
          box = document.createElement('section');
          box.className = 'vpscs-admin-subscriptions';
          dialogScrollBody(dialog).prepend(box);
        }
        box.dataset.signature = signature;
        box.dataset.index = String(index);
        box.innerHTML = '<strong>订阅（共 ' + profiles.length + ' 条链接）</strong><select></select><div class="vpscs-admin-subscription-detail"></div>';
        var select = box.querySelector('select');
        profiles.forEach(function (item, i) {
          var option = document.createElement('option');
          option.value = String(i);
          option.textContent = profileLabel(item, i) + (profiles.length > 1 && i === 0 ? '（当前）' : '');
          if (i === index) option.selected = true;
          select.appendChild(option);
        });
        select.addEventListener('change', function () {
          var next = Math.max(0, Math.min(Number(select.value) || 0, profiles.length - 1));
          box.dataset.index = String(next);
          paintSubscriptionDetail(box, next);
        });
        paintSubscriptionDetail(box, index);
      }

      // Users who bought several plans get independent subscription rows.
      // The list itself only prints the primary plan, so add a compact picker
      // to that cell; the selected id is what the mutation hooks below target.
      // 后台实际把登录票据放在 VUE_NAIVE_ACCESS_TOKEN（值是 {value: 'xx|yy'}）；
      // 早期版本叫 XBOARD_ACCESS_TOKEN。两个键可能同时存在，而且**旧的那个已经失效**
      // （带着它请求一律 403 Unauthorized），所以不能写死顺序，取最近写入、没过期的那条。
      function adminToken() {
        var keys = ['XBOARD_ACCESS_TOKEN', 'VUE_NAIVE_ACCESS_TOKEN'];
        var best = '';
        var bestTime = -1;
        for (var i = 0; i < keys.length; i += 1) {
          var raw = localStorage.getItem(keys[i]);
          if (!raw) continue;
          var parsed = raw;
          try { parsed = JSON.parse(raw); } catch (_) {}
          var stamp = 0;
          if (parsed && typeof parsed === 'object') {
            stamp = Number(parsed.time || 0);
            if (parsed.expire && Number(parsed.expire) < Date.now()) continue;
            parsed = parsed.value || parsed.token || '';
          }
          var value = String(parsed || '').trim();
          if (!value) continue;
          if (stamp > bestTime) { bestTime = stamp; best = value; }
        }
        return best;
      }
      // sanctum 只认 `Authorization: Bearer <token>`，裸 token 一律 401。
      function authHeader(token) {
        if (!token) return '';
        return /^Bearer\s/i.test(token) ? token : 'Bearer ' + token;
      }

      // The admin list sometimes renders before our capture hooks see the
      // response (fetch adapter, cloned body, route rebuilt...).  Fetch the same
      // endpoint ourselves with the stored admin token instead of relying on it.
      var userListFetchedAt = 0;
      function loadAdminUsers(token) {
        if (adminUsers.length || Date.now() - userListFetchedAt < 2000) return;
        userListFetchedAt = Date.now();
        var secure = (window.settings && window.settings.secure_path) || '';
        if (!token || !secure) return;
        nativeFetch('/api/v2/' + secure + '/user/fetch?current=1&pageSize=100', {
          headers: { Authorization: authHeader(token), 'Content-Language': 'zh-CN' },
          credentials: 'same-origin',
        }).then(function (response) { return response.text(); }).then(applyUserListText).catch(function () {});
      }

      // 管理员保存过订阅以后（改到期/流量/删订阅），重新拉一次列表：
      // 备注号和日期立刻跟着变，过期的订阅也会自动从选择框里消失。
      var userListRefreshTimer = null;
      function scheduleUserListRefresh() {
        if (userListRefreshTimer) clearTimeout(userListRefreshTimer);
        userListRefreshTimer = setTimeout(function () {
          userListRefreshTimer = null;
          adminUsers = [];
          userListFetchedAt = 0;
          loadAdminUsers(adminToken());
        }, 600);
      }

      // ---- 工单邮件通知 ----
      // 工单管理页顶部插一张卡片：填收件邮箱，用户提交新工单就发邮件提醒。
      // 发信走站点自己的 SMTP（系统设置 → 邮件），不用另建邮箱。
      var ticketNotify = { loaded: false, loading: false, emails: [], smtpReady: false, from: '', smtp: {} };

      function ticketEndpoint(path) {
        var secure = (window.settings && window.settings.secure_path) || '';
        return '/api/v2/' + secure + path;
      }

      function ticketRequest(method, path, body) {
        var headers = { Authorization: authHeader(adminToken()), 'Content-Language': 'zh-CN' };
        if (body) headers['Content-Type'] = 'application/json';
        return nativeFetch(ticketEndpoint(path), {
          method: method,
          headers: headers,
          credentials: 'same-origin',
          body: body ? JSON.stringify(body) : undefined,
        }).then(function (response) {
          return response.text().then(function (text) {
            var payload = {};
            try { payload = JSON.parse(text); } catch (_) {}
            if (!response.ok || payload.status === 'fail') {
              throw new Error(payload.message || ('请求失败（' + response.status + '）'));
            }
            return payload.data;
          });
        });
      }

      // 工单列表的表头是「工单号 / 主题 / 优先级 / 状态」，用表头认页面，
      // 不会误伤用户列表（那个是「邮箱 / 订阅」）。
      function ticketTable() {
        var tables = document.querySelectorAll('#root table');
        for (var i = 0; i < tables.length; i += 1) {
          var head = tables[i].querySelector('thead');
          if (!head) continue;
          var text = head.textContent.replace(/\s+/g, '');
          if (/工单号/.test(text) || (/主题/.test(text) && /优先级/.test(text))) return tables[i];
        }
        return null;
      }

      function ticketNotifyStatus(message, tone) {
        var panel = document.getElementById('vpscs-ticket-notify');
        if (!panel) return;
        var node = panel.querySelector('.vpscs-tn-status');
        node.textContent = message || '';
        node.className = 'vpscs-tn-status' + (tone ? ' is-' + tone : '');
      }

      function paintTicketNotifyState() {
        var panel = document.getElementById('vpscs-ticket-notify');
        if (!panel) return;
        var badge = panel.querySelector('.vpscs-tn-badge');
        if (!ticketNotify.smtpReady) {
          badge.textContent = '未配置发信';
          badge.className = 'vpscs-tn-badge is-warn';
          ticketNotifyStatus('还没配置发信服务器：到「系统设置 → 邮件」填好 SMTP（和下面这份是同一套配置），保存后发一封测试邮件确认。', 'warn');
        } else if (ticketNotify.emails.length) {
          badge.textContent = '已开启 · ' + ticketNotify.emails.length + ' 个邮箱';
          badge.className = 'vpscs-tn-badge is-ok';
          ticketNotifyStatus('新工单会立即发到：' + ticketNotify.emails.join('、') + (ticketNotify.from ? '（发件人 ' + ticketNotify.from + '）' : ''), 'ok');
        } else {
          badge.textContent = '待填邮箱';
          badge.className = 'vpscs-tn-badge';
          ticketNotifyStatus('填一个邮箱保存后即开启通知。', '');
        }
        var summary = panel.querySelector('.vpscs-tn-smtp-summary');
        if (summary) {
          var host = (ticketNotify.smtp && ticketNotify.smtp.host) || '';
          summary.textContent = host
            ? '发信服务器：' + host + ((ticketNotify.smtp && ticketNotify.smtp.port) ? ':' + ticketNotify.smtp.port : '')
            : '发信服务器设置（SMTP，用自己网站域名邮箱即可）';
        }
      }

      function fillTicketNotifyForm(panel) {
        var smtp = ticketNotify.smtp || {};
        var map = { host: smtp.host, port: smtp.port, encryption: smtp.encryption, username: smtp.username, from: smtp.from };
        Object.keys(map).forEach(function (key) {
          var field = panel.querySelector('.vpscs-tn-smtp [data-field="' + key + '"]');
          if (!field || document.activeElement === field) return;
          field.value = map[key] || '';
        });
        var password = panel.querySelector('.vpscs-tn-smtp [data-field="password"]');
        if (password && document.activeElement !== password) {
          password.value = '';
          password.placeholder = smtp.has_password ? '已设置，留空表示不修改' : 'SMTP 密码 / 授权码';
        }
        var emails = panel.querySelector('.vpscs-tn-input');
        if (emails && document.activeElement !== emails) emails.value = ticketNotify.emails.join(', ');
      }

      function buildTicketNotifyPanel(table) {
        var panel = document.createElement('section');
        panel.id = 'vpscs-ticket-notify';
        panel.className = 'vpscs-tn';
        panel.innerHTML = '<div class="vpscs-tn-head"><strong class="vpscs-tn-title">工单邮件通知</strong><span class="vpscs-tn-badge">待填邮箱</span></div>'
          + '<p class="vpscs-tn-desc">用户提交新工单时立刻发邮件到下面的地址。发信走本站的 SMTP，用自己域名的邮箱即可，不需要另建服务。</p>'
          + '<div class="vpscs-tn-row"><input class="vpscs-tn-input" type="text" spellcheck="false" placeholder="接收通知的邮箱，例如 admin@your-domain.com, ops@your-domain.com">'
          + '<button type="button" class="vpscs-tn-btn is-primary" data-act="save">保存</button>'
          + '<button type="button" class="vpscs-tn-btn" data-act="test">发送测试邮件</button></div>'
          + '<details class="vpscs-tn-smtp"><summary class="vpscs-tn-smtp-summary">发信服务器设置（SMTP，用自己网站域名邮箱即可）</summary>'
          + '<div class="vpscs-tn-grid">'
          + '<label>SMTP 服务器<input data-field="host" type="text" spellcheck="false" placeholder="smtp.exmail.qq.com"></label>'
          + '<label>端口<input data-field="port" type="text" inputmode="numeric" placeholder="465"></label>'
          + '<label>加密<select data-field="encryption"><option value="">自动</option><option value="ssl">SSL</option><option value="tls">TLS</option></select></label>'
          + '<label>账号<input data-field="username" type="text" spellcheck="false" placeholder="admin@your-domain.com"></label>'
          + '<label>密码 / 授权码<input data-field="password" type="password" spellcheck="false" placeholder="SMTP 密码 / 授权码"></label>'
          + '<label>发件人地址<input data-field="from" type="text" spellcheck="false" placeholder="admin@your-domain.com"></label>'
          + '</div><small class="vpscs-tn-tip">常见填法：腾讯企业邮 smtp.exmail.qq.com:465(SSL)；阿里企业邮 smtp.qiye.aliyun.com:465(SSL)；Gmail smtp.gmail.com:587(TLS，需应用专用密码)。密码留空表示保持原来的不变。</small></details>'
          + '<p class="vpscs-tn-status"></p>';

        function collectSmtp() {
          var read = function (field) {
            var node = panel.querySelector('.vpscs-tn-smtp [data-field="' + field + '"]');
            return node ? node.value.trim() : '';
          };
          return {
            host: read('host'), port: read('port'), encryption: read('encryption'),
            username: read('username'), password: read('password'), from: read('from'),
          };
        }

        panel.addEventListener('click', function (event) {
          var button = event.target.closest('[data-act]');
          if (!button) return;
          var act = button.getAttribute('data-act');
          if (act === 'save') {
            button.disabled = true;
            ticketNotifyStatus('正在保存…', '');
            var smtpDraft = collectSmtp();
            var payload = { emails: panel.querySelector('.vpscs-tn-input').value };
            // 只在真的填了发信服务器时才回传，避免只改收件邮箱把 SMTP 清空。
            if (smtpDraft.host || smtpDraft.username || smtpDraft.from) payload.smtp = smtpDraft;
            ticketRequest('POST', '/ticket/notify', payload).then(function (data) {
              ticketNotify.emails = (data && data.emails) || [];
              ticketNotify.smtpReady = !!(data && data.smtp_ready);
              ticketNotify.from = (data && data.from) || '';
              ticketNotify.smtp = collectSmtp();
              ticketNotify.smtp.has_password = true;
              var password = panel.querySelector('.vpscs-tn-smtp [data-field="password"]');
              if (password) { password.value = ''; password.placeholder = '已设置，留空表示不修改'; }
              paintTicketNotifyState();
              ticketNotifyStatus('已保存。', 'ok');
            }).catch(function (error) {
              ticketNotifyStatus(error.message || '保存失败', 'error');
            }).finally(function () { button.disabled = false; });
            return;
          }
          if (act === 'test') {
            if (!ticketNotify.emails.length) {
              ticketNotifyStatus('请先填写并保存收件邮箱。', 'warn');
              return;
            }
            button.disabled = true;
            ticketNotifyStatus('正在发送测试邮件…', '');
            ticketRequest('POST', '/ticket/notify/test').then(function (data) {
              var results = (data && data.results) || [];
              var failed = results.filter(function (item) { return item.error; });
              if (failed.length) {
                ticketNotifyStatus('部分发送失败：' + failed.map(function (item) { return item.email + '（' + item.error + '）'; }).join('；'), 'error');
              } else {
                ticketNotifyStatus('测试邮件已发出，请检查收件箱（也看一下垃圾箱）。', 'ok');
              }
            }).catch(function (error) {
              ticketNotifyStatus(error.message || '测试邮件发送失败', 'error');
            }).finally(function () { button.disabled = false; });
          }
        });

        // 放在「页面标题 + 说明」那一行的下面，找不到就放在表格上方。
        // 用标题和表格的公共祖先当页面容器，避免插进 flex 标题行里把布局挤乱。
        var heading = Array.prototype.slice.call(document.querySelectorAll('#root h1, #root h2, #root h3, #root [class*="text-xl"], #root [class*="text-2xl"]'))
          .find(function (node) { return node.textContent.trim() === '工单管理' && !node.closest('aside,nav'); });
        var container = table;
        if (heading) {
          while (container && container !== document.body && !container.contains(heading)) {
            container = container.parentElement;
          }
        }
        if (heading && container && container !== document.body && container.id !== 'root' && container.children.length > 1) {
          var headerBlock = null;
          Array.prototype.forEach.call(container.children, function (child) {
            if (!headerBlock && child.contains(heading)) headerBlock = child;
          });
          container.insertBefore(panel, headerBlock ? headerBlock.nextSibling : container.firstChild);
        } else {
          table.parentNode.insertBefore(panel, table);
        }
        return panel;
      }

      function renderTicketNotifier() {
        var table = ticketTable();
        var panel = document.getElementById('vpscs-ticket-notify');
        if (!table) { if (panel) panel.remove(); return; }
        if (!panel) panel = buildTicketNotifyPanel(table);
        if (ticketNotify.loaded || ticketNotify.loading) return;
        ticketNotify.loading = true;
        ticketRequest('GET', '/ticket/notify').then(function (data) {
          ticketNotify.emails = (data && data.emails) || [];
          ticketNotify.smtpReady = !!(data && data.smtp_ready);
          ticketNotify.from = (data && data.from) || '';
          ticketNotify.smtp = (data && data.smtp) || {};
          ticketNotify.loaded = true;
          fillTicketNotifyForm(panel);
          paintTicketNotifyState();
        }).catch(function (error) {
          ticketNotifyStatus(error.message || '读取通知设置失败', 'error');
        }).finally(function () { ticketNotify.loading = false; });
      }

      function refreshAdminEnhancements() {
        // 订阅面板的数据来自 /user/fetch（里面带 subscription_profiles），
        // 列表还没加载完时先补一次请求，否则弹窗里没有可编辑的订阅。
        if (!adminUsers.length) loadAdminUsers(adminToken());
        paintRowProfiles();
        renderSubscriptionPicker();
        renderTicketNotifier();
      }
      new MutationObserver(refreshAdminEnhancements).observe(document.body, { childList: true, subtree: true });
      document.addEventListener('click', function (event) {
        var action = event.target.closest('button,a,[role="menuitem"]');
        if (!action || !/复制订阅URL/.test(action.textContent)) return;
        var selectedId = Number(sessionStorage.getItem('vpscs_admin_subscription_id'));
        var profile = profiles.find(function (item) { return Number(item.id) === selectedId; });
        if (!profile || !profile.subscribe_url) return;
        event.preventDefault(); event.stopImmediatePropagation();
        navigator.clipboard.writeText(profile.subscribe_url);
      }, true);
    })();
  </script>
  <style>
    .vpscs-plan-type { margin: 0 0 16px; padding: 14px; border: 1px solid var(--el-border-color, #dcdfe6); border-radius: 8px; display: grid; gap: 8px; }
    .vpscs-plan-type label { font-weight: 600; }
    .vpscs-plan-type select { min-height: 38px; padding: 0 10px; border: 1px solid var(--el-border-color, #dcdfe6); border-radius: 6px; background: var(--el-bg-color, #fff); color: var(--el-text-color-primary, #303133); }
    .vpscs-plan-type small { color: var(--el-text-color-secondary, #909399); }
    .vpscs-admin-subscriptions { margin:0 0 18px; padding:14px; display:grid; gap:10px; border:1px solid var(--el-border-color); border-radius:9px; }
    .vpscs-admin-subscriptions select, .vpscs-admin-subscriptions input { min-height:38px; padding:0 10px; color:var(--el-text-color-primary); background:var(--el-bg-color); border:1px solid var(--el-border-color); border-radius:7px; }
    .vpscs-admin-subscription-detail { display:grid; gap:6px; }
    .vpscs-sub-line { display:flex; flex-wrap:wrap; align-items:center; gap:8px; font-size:12.5px; line-height:1.6; }
    .vpscs-sub-line.is-strong { font-weight:600; }
    .vpscs-sub-line.is-muted { color:var(--el-text-color-secondary, #8a93a6); font-size:12px; }
    .vpscs-sub-key { min-width:34px; color:var(--el-text-color-secondary, #8a93a6); }
    .vpscs-sub-edit input { min-height:32px; height:32px; padding:0 8px; width:92px; }
    .vpscs-sub-edit button { min-height:32px; padding:0 10px; font-size:12px; cursor:pointer; color:var(--el-text-color-primary, #dbe4f2); background:transparent; border:1px solid var(--el-border-color, #2b3a52); border-radius:7px; }
    .vpscs-sub-edit button:hover:not(:disabled) { border-color:#7c5cff; color:#a793ff; }
    .vpscs-sub-edit button.is-primary { border-color:#7c5cff; background:rgba(124,92,255,.16); }
    .vpscs-sub-edit button.is-danger { border-color:rgba(240,89,107,.55); color:#f0596b; }
    .vpscs-sub-edit button.is-danger:hover:not(:disabled) { background:rgba(240,89,107,.14); }
    .vpscs-sub-edit button:disabled { opacity:.55; cursor:not-allowed; }
    .vpscs-sub-unit { color:var(--el-text-color-secondary, #8a93a6); }
    .vpscs-sub-url { width:100%; min-height:34px; }
    .vpscs-sub-status { min-height:16px; font-size:12px; color:var(--el-text-color-secondary, #8a93a6); }
    .vpscs-sub-status.is-ok { color:#12b886; }
    /* 邮箱前的「N 条」订阅标签：用 ::before 画，不往 React 的节点里插元素 */
    #root table td [data-vpscs-count]::before {
      content: attr(data-vpscs-count);
      margin-right: 2px;
      padding: 1px 7px;
      font-size: 10.5px;
      font-weight: 700;
      line-height: 16px;
      color: hsl(var(--muted-foreground));
      background: hsl(var(--secondary));
      border: 1px solid hsl(var(--border));
      border-radius: 999px;
      white-space: nowrap;
      font-variant-numeric: tabular-nums;
    }
    .vpscs-sub-status.is-error { color:#f0596b; }
    .vpscs-confirm-mask { position:fixed; inset:0; z-index:3000; display:flex; align-items:center; justify-content:center; background:rgba(6,10,18,.55); }
    .vpscs-confirm { width:320px; padding:18px; display:grid; gap:14px; border:1px solid var(--el-border-color); border-radius:12px; background:var(--el-bg-color-overlay, #131c2c); box-shadow:0 18px 40px rgba(0,0,0,.35); }
    .vpscs-confirm p { margin:0; font-size:13px; line-height:1.7; color:var(--el-text-color-primary); }
    .vpscs-confirm-actions { display:flex; justify-content:flex-end; gap:10px; }
    .vpscs-confirm-actions button { min-height:32px; padding:0 14px; font-size:12.5px; cursor:pointer; color:var(--el-text-color-primary); background:transparent; border:1px solid var(--el-border-color, #2b3a52); border-radius:7px; }
    .vpscs-confirm-actions button.is-danger { border-color:rgba(240,89,107,.6); color:#f0596b; }
    .vpscs-confirm-actions button.is-danger:hover { background:rgba(240,89,107,.14); }
    /* 工单邮件通知卡片 */
    .vpscs-tn { margin:0 0 18px; padding:16px 18px; display:grid; gap:10px; border:1px solid hsl(var(--border, 214 20% 28%)); border-radius:12px; background:hsl(var(--card, 222 30% 13%)); color:hsl(var(--card-foreground, 210 30% 92%)); }
    .vpscs-tn-head { display:flex; align-items:center; gap:10px; }
    .vpscs-tn-title { font-size:14px; font-weight:700; }
    .vpscs-tn-badge { padding:2px 9px; border:1px solid hsl(var(--border, 214 20% 28%)); border-radius:999px; font-size:11px; font-weight:700; color:hsl(var(--muted-foreground, 215 15% 65%)); }
    .vpscs-tn-badge.is-ok { border-color:rgba(0,168,120,.55); color:#12b886; background:rgba(0,168,120,.12); }
    .vpscs-tn-badge.is-warn { border-color:rgba(255,157,0,.5); color:#e8a33d; background:rgba(255,157,0,.12); }
    .vpscs-tn-desc { margin:0; font-size:12px; line-height:1.65; color:hsl(var(--muted-foreground, 215 15% 65%)); }
    .vpscs-tn-row { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
    .vpscs-tn-input { flex:1 1 320px; min-height:36px; padding:0 12px; font-size:13px; color:hsl(var(--foreground, 210 30% 92%)); background:hsl(var(--background, 222 30% 10%)); border:1px solid hsl(var(--border, 214 20% 28%)); border-radius:8px; }
    .vpscs-tn-input:focus { outline:none; border-color:#7c5cff; box-shadow:0 0 0 2px rgba(124,92,255,.18); }
    .vpscs-tn-btn { min-height:36px; padding:0 14px; font-size:13px; font-weight:600; color:hsl(var(--foreground, 210 30% 92%)); background:transparent; border:1px solid hsl(var(--border, 214 20% 28%)); border-radius:8px; cursor:pointer; }
    .vpscs-tn-btn:hover:not(:disabled) { background:rgba(124,92,255,.10); }
    .vpscs-tn-btn:disabled { opacity:.55; cursor:default; }
    .vpscs-tn-btn.is-primary { color:hsl(var(--primary-foreground, 210 40% 98%)); background:hsl(var(--primary, 221 83% 53%)); border-color:transparent; }
    .vpscs-tn-status { margin:0; min-height:16px; font-size:12px; line-height:1.6; color:hsl(var(--muted-foreground, 215 15% 65%)); }
    .vpscs-tn-status.is-ok { color:#12b886; }
    .vpscs-tn-status.is-warn { color:#e8a33d; }
    .vpscs-tn-status.is-error { color:#f0596b; }
    .vpscs-tn-smtp { padding-top:10px; border-top:1px dashed hsl(var(--border, 214 20% 28%)); }
    .vpscs-tn-smtp-summary { cursor:pointer; font-size:12px; font-weight:600; color:hsl(var(--muted-foreground, 215 15% 65%)); }
    .vpscs-tn-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; margin-top:12px; }
    .vpscs-tn-grid label { display:grid; gap:5px; font-size:12px; color:hsl(var(--muted-foreground, 215 15% 65%)); }
    .vpscs-tn-grid input, .vpscs-tn-grid select { min-height:34px; padding:0 10px; font-size:13px; color:hsl(var(--foreground, 210 30% 92%)); background:hsl(var(--background, 222 30% 10%)); border:1px solid hsl(var(--border, 214 20% 28%)); border-radius:8px; }
    .vpscs-tn-tip { display:block; margin-top:10px; font-size:11px; line-height:1.7; color:hsl(var(--muted-foreground, 215 15% 65%)); }
    @media (max-width:900px) { .vpscs-tn-grid { grid-template-columns:1fr 1fr; } }
    html.dark #root .bg-white, html.dark #root [class~="bg-white"],
    html.dark #root .bg-gray-50, html.dark #root [class~="bg-gray-50"],
    html.dark #root .bg-green-50, html.dark #root [class~="bg-green-50"] {
      background-color: hsl(var(--secondary)) !important;
      color: hsl(var(--secondary-foreground)) !important;
    }
    html.dark #root .text-green-700, html.dark #root .text-gray-700,
    html.dark #root .text-gray-900 { color: hsl(var(--foreground)) !important; }
  </style>
  <script>
    window.vpscsSoftwareAdmin = {
      apiBase: @json('/api/v2/' . $secure_path . '/software'),
      version: '{{ $swVersion }}'
    };
  </script>
  <script defer src="/assets/software-admin.js?v={{ $swVersion }}"></script>
</body>

</html>
