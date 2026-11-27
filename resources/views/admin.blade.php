<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $title }}</title>
  <script>
    document.documentElement.classList.add('dark', 'vpscs-admin-dark');
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
    :root, html.dark {
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
    html, body, #root, .el-container, .el-main {
      background: #080d17 !important;
      color: #e8edf6 !important;
    }
    .el-aside, .el-header, .el-menu, .el-menu--popup, .el-card,
    .el-dialog, .el-dialog__body, .el-drawer, .el-drawer__body,
    .el-popover.el-popper, .el-dropdown__popper, .el-select__popper,
    .el-picker-panel, .el-message-box {
      background: #0f1828 !important;
      color: #e8edf6 !important;
      border-color: #293750 !important;
    }
    .el-card, .el-table, .el-table tr, .el-table th.el-table__cell,
    .el-table td.el-table__cell, .el-table__expanded-cell,
    .el-descriptions__body, .el-descriptions__table, .el-pagination {
      background: #0f1828 !important;
      color: #dbe4f2 !important;
      border-color: #293750 !important;
    }
    .el-table th.el-table__cell,
    .el-table--enable-row-hover .el-table__body tr:hover > td.el-table__cell,
    .el-menu-item:hover, .el-sub-menu__title:hover {
      background: #18243a !important;
    }
    .el-input__wrapper, .el-textarea__inner, .el-select__wrapper,
    .el-input-number, .el-date-editor, .el-upload-dragger {
      background: #101a2b !important;
      color: #eef2f8 !important;
      box-shadow: 0 0 0 1px #2b3a52 inset !important;
      border-color: #2b3a52 !important;
    }
    .el-input__inner, .el-textarea__inner, .el-select__selected-item,
    .el-form-item__label, .el-dialog__title, .el-drawer__title,
    .el-card__header, .el-menu-item, .el-sub-menu__title,
    h1, h2, h3, h4, h5, h6 { color: #eef2f8 !important; }
    .el-menu-item.is-active, a { color: #9f8cff !important; }
    .el-empty__description, .el-text, small, .text-gray-500 {
      color: #9caac0 !important;
    }
    .el-table::before, .el-table__inner-wrapper::before, .el-card,
    .el-dialog, .el-drawer { border-color: #293750 !important; }
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
  </script>
</body>

</html>
