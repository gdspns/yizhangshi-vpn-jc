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
        refreshAdminEnhancements();
      }
      function applyUserListText(text) {
        try { applyUserList(JSON.parse(text)); } catch (_) {}
      }
      window.fetch = function (input, init) {
        var url = typeof input === 'string' ? input : input.url;
        var selectedId = sessionStorage.getItem('vpscs_admin_subscription_id');
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
        var selectedId = sessionStorage.getItem('vpscs_admin_subscription_id');
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
        var selectedId = sessionStorage.getItem('vpscs_admin_subscription_id');
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
        // Rebuilding on every mutation used to reset the selection (and could
        // loop forever because innerHTML itself is a mutation).  Rebuild only
        // when the subscription set actually changes.
        var signature = subscriptionSignature(profiles);
        if (box && box.dataset.signature === signature) return;
        if (!box) { box = document.createElement('section'); box.className = 'vpscs-admin-subscriptions'; var body = dialog.querySelector('.el-dialog__body') || dialog; body.prepend(box); }
        box.dataset.signature = signature;
        box.innerHTML = '<strong>订阅（共 ' + profiles.length + ' 条链接）</strong><select>' + profiles.map(function (item, index) { return '<option value="' + index + '">' + profileLabel(item, index) + '</option>'; }).join('') + '</select><div class="vpscs-admin-subscription-detail"></div>';
        var select = box.querySelector('select');
        function show() { var item = profiles[Number(select.value)] || {}; sessionStorage.setItem('vpscs_admin_subscription_id', item.id || ''); var used = (Number(item.u || 0) + Number(item.d || 0)) / 1073741824; var total = Number(item.transfer_enable || 0) / 1073741824; box.querySelector('.vpscs-admin-subscription-detail').innerHTML = '<div>当前操作目标：订阅' + profileNo(item, Number(select.value)) + '（' + (item.name || '订阅') + '，ID ' + item.id + '）</div><div>套餐：' + (item.plan && item.plan.name || '-') + '</div><div>流量：' + used.toFixed(2) + ' / ' + total.toFixed(2) + ' GB</div><div>到期：' + (item.expired_at ? new Date(Number(item.expired_at) * 1000).toLocaleDateString() : '长期有效') + '</div><input readonly value="' + (item.subscribe_url || '') + '">'; }
        select.addEventListener('change', show); show();
      }

      // Users who bought several plans get independent subscription rows.
      // The list itself only prints the primary plan, so add a compact picker
      // to that cell; the selected id is what the mutation hooks below target.
      function adminToken() {
        try { return JSON.parse(localStorage.getItem('XBOARD_ACCESS_TOKEN') || '{}').value || ''; } catch (_) { return ''; }
      }

      function renderRowSubscriptionPickers() {
        if (!adminUsers.length) {
          if (!document.querySelector('#root table')) return;
          loadAdminUsers(adminToken());
          return;
        }
        // The admin page can hold more than one table (sticky header copies,
        // statistics panels).  Pick the one whose head actually has both an
        // email and a subscription column.
        var tables = Array.prototype.slice.call(document.querySelectorAll('#root table'));
        var table = null;
        var index = -1;
        var emailIndex = -1;
        tables.forEach(function (candidate) {
          if (table) return;
          var headers = Array.prototype.slice.call(candidate.querySelectorAll('thead th'));
          if (!headers.length) return;
          var subscriptionIndex = -1;
          var mailIndex = -1;
          headers.forEach(function (th, i) {
            var text = th.textContent.replace(/\s+/g, '');
            if (subscriptionIndex === -1 && /订阅|subscription/i.test(text)) subscriptionIndex = i;
            if (mailIndex === -1 && /邮箱|email/i.test(text)) mailIndex = i;
          });
          if (subscriptionIndex !== -1) { table = candidate; index = subscriptionIndex; emailIndex = mailIndex; }
        });
        if (!table || index === -1) return;
        Array.prototype.slice.call(table.querySelectorAll('tbody tr')).forEach(function (row) {
          var cell = row.children[index];
          if (!cell) return;
          // Read the email from its own cell: the row text glues the ID in front
          // of it and the device counter behind it ("1admin@admin.com0 / 10").
          var emailCell = emailIndex >= 0 ? row.children[emailIndex] : null;
          var emailMatch = ((emailCell && emailCell.textContent) || row.textContent || '').match(/[\w.+-]+@[\w-]+(?:\.[\w-]+)+/);
          if (!emailMatch) return;
          var user = adminUsers.find(function (item) { return item.email === emailMatch[0]; });
          var items = liveProfiles(user && Array.isArray(user.subscription_profiles) ? user.subscription_profiles : []);
          // Row actions (reset password, edit, delete) must target the row the
          // administrator clicked, otherwise a previously selected subscription
          // from another user would be rewritten into the request.
          if (row.dataset.vpscsSubBound !== '1') {
            row.dataset.vpscsSubBound = '1';
            row.addEventListener('click', function () {
              var picker = row.querySelector('.vpscs-row-sub select');
              var id = picker ? picker.value : String((items[0] || {}).id || '');
              if (id) sessionStorage.setItem('vpscs_admin_subscription_id', id);
            }, true);
          }
          var existing = cell.querySelector('.vpscs-row-sub');
          if (items.length < 2) { if (existing) existing.remove(); return; }
          var signature = subscriptionSignature(items);
          // 到期时间/流量被改过以后，选择框里的备注号和日期要跟着变，不能等刷新页面。
          if (existing) {
            if (existing.dataset.signature === signature) return;
            fillRowPicker(existing, items, signature);
            return;
          }
          var wrap = document.createElement('div');
          wrap.className = 'vpscs-row-sub';
          fillRowPicker(wrap, items, signature);
          cell.appendChild(wrap);
        });
      }

      // 复用同一个 div：只重写选项，保留当前选择。
      function fillRowPicker(wrap, items, signature) {
        var previous = wrap.querySelector('select');
        var keep = previous ? previous.value : '';
        var select = document.createElement('select');
        select.title = '选择要管理的订阅（共 ' + items.length + ' 条）';
        items.forEach(function (item, i) {
          var option = document.createElement('option');
          option.value = String(item.id || '');
          option.textContent = profileLabel(item, i);
          select.appendChild(option);
        });
        var keepAlive = items.some(function (item) { return String(item.id) === keep; });
        select.value = keepAlive ? keep : String((items[0] || {}).id || '');
        select.classList.add('is-picked');
        select.addEventListener('change', function () {
          sessionStorage.setItem('vpscs_admin_subscription_id', select.value);
        });
        wrap.dataset.signature = signature;
        wrap.replaceChildren(select);
        // 之前选中的订阅到期被隐藏了：把操作目标挪到第一条，别让后续请求打到过期档案上。
        var previousIds = previous ? Array.prototype.map.call(previous.options, function (option) { return option.value; }) : [];
        var stored = sessionStorage.getItem('vpscs_admin_subscription_id');
        if (!keepAlive && previousIds.indexOf(keep) !== -1 && stored === keep) {
          sessionStorage.setItem('vpscs_admin_subscription_id', select.value);
        }
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
          headers: { Authorization: token, 'Content-Language': 'zh-CN' },
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
        var headers = { Authorization: adminToken(), 'Content-Language': 'zh-CN' };
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
          ticketNotifyStatus('还没配置发信服务器：展开下面的「发信服务器设置」填好 SMTP（用本站域名邮箱即可），保存后发一封测试邮件确认。', 'warn');
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
        renderSubscriptionPicker();
        renderRowSubscriptionPickers();
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
    .vpscs-row-sub { margin-top:6px; }
    .vpscs-row-sub select { max-width:190px; min-height:26px; padding:0 6px; font-size:12px; color:var(--el-text-color-primary, #dbe4f2); background:var(--el-bg-color, #101a2b); border:1px solid var(--el-border-color, #2b3a52); border-radius:6px; }
    .vpscs-row-sub select.is-picked { border-color:#7c5cff; box-shadow:0 0 0 2px rgba(124,92,255,.18); }
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
