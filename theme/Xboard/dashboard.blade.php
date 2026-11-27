<!doctype html>
<html lang="zh-CN">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no" />
  <title>{{$title}}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/theme/{{$theme}}/assets/xigua.css?v={{$version}}" />
  <script defer src="/theme/{{$theme}}/assets/xigua.js?v={{$version}}"></script>
  <script type="module" crossorigin src="/theme/{{$theme}}/assets/umi.js"></script>
</head>
<style>
  .markdown-body { white-space: pre-line; }
  .markdown-body p { margin: 0 0 .55rem; }
  .n-button.n-button--primary-type {
    opacity: 1 !important;
    filter: none !important;
    color: #fff !important;
    background: #7c3aed !important;
    border-color: #7c3aed !important;
    font-weight: 700 !important;
    box-shadow: 0 8px 18px rgba(124, 92, 255, .28) !important;
  }
  .n-button.n-button--primary-type .n-button__content { color: #fff !important; }
  .n-button.n-button--primary-type:hover { background: #6d28d9 !important; }
</style>

<body>

  <script>
    window.routerBase = "/";
    window.settings = {
      title: '{{$title}}',
      assets_path: '/theme/{{$theme}}/assets',
      theme: {
        color: '{{ $theme_config['theme_color'] ?? "default" }}',
      },
      version: '{{$version}}',
      background_url: '{{$theme_config['background_url']}}',
      description: '{{$description}}',
      i18n: [
        'zh-CN',
        'en-US',
        'ja-JP',
        'vi-VN',
        'ko-KR',
        'zh-TW',
        'fa-IR'
      ],
      logo: '{{$logo}}'
    }
  </script>
  <div id="app"></div>
  {!! $theme_config['custom_html'] !!}
</body>

</html>
