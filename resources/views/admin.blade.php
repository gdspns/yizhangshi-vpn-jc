<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $title }}</title>
  <script>
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
  </style>
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
            renderSubscriptionPicker();
          }).catch(function () {});
          return response;
        });
      };
      function renderSubscriptionPicker() {
        var dialog = Array.from(document.querySelectorAll('[role="dialog"]')).find(function (item) { return /用户|订阅/.test(item.textContent); });
        if (!dialog || !profiles.length) return;
        var box = dialog.querySelector('.vpscs-admin-subscriptions');
        if (!box) { box = document.createElement('section'); box.className = 'vpscs-admin-subscriptions'; var body = dialog.querySelector('.el-dialog__body') || dialog; body.prepend(box); }
        box.innerHTML = '<strong>订阅（共 ' + profiles.length + ' 条链接）</strong><select>' + profiles.map(function (item, index) { return '<option value="' + index + '">' + (item.name || '订阅 ' + (index + 1)) + '</option>'; }).join('') + '</select><div class="vpscs-admin-subscription-detail"></div>';
        var select = box.querySelector('select');
        function show() { var item = profiles[Number(select.value)] || {}; sessionStorage.setItem('vpscs_admin_subscription_id', item.id || ''); var used = (Number(item.u || 0) + Number(item.d || 0)) / 1073741824; var total = Number(item.transfer_enable || 0) / 1073741824; box.querySelector('.vpscs-admin-subscription-detail').innerHTML = '<div>当前操作目标：' + (item.name || '订阅') + '（ID ' + item.id + '）</div><div>套餐：' + (item.plan && item.plan.name || '-') + '</div><div>流量：' + used.toFixed(2) + ' / ' + total.toFixed(2) + ' GB</div><div>到期：' + (item.expired_at ? new Date(Number(item.expired_at) * 1000).toLocaleDateString() : '长期有效') + '</div><input readonly value="' + (item.subscribe_url || '') + '">'; }
        select.addEventListener('change', show); show();
      }
      new MutationObserver(renderSubscriptionPicker).observe(document.body, { childList: true, subtree: true });
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
  </style>
</body>

</html>
