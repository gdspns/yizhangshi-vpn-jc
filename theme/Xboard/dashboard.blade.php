<!doctype html>
<html lang="zh-CN" class="dark">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no" />
  <title>{{$title}}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@php
  // 用静态资源的 mtime+size 做指纹：每次构建产物变化，URL 就变，CDN 与浏览器缓存自动失效。
  // 不能用 $version：同一天多次部署 getCurrentVersion() 不会变，Cloudflare 会一直回旧文件。
  $assetDir = public_path('theme/' . $theme . '/assets/');
  $assetSig = @filemtime($assetDir . 'umi.js') . '|' . @filesize($assetDir . 'umi.js')
      . '|' . @filemtime($assetDir . 'xigua.css') . '|' . @filesize($assetDir . 'xigua.css')
      . '|' . @filemtime($assetDir . 'xigua.js') . '|' . @filesize($assetDir . 'xigua.js');
  $assetVer = substr(md5((string) $assetSig), 0, 10);
@endphp
  <link rel="stylesheet" href="/theme/{{$theme}}/assets/xigua.css?v={{$assetVer}}" />
  <script defer src="/theme/{{$theme}}/assets/xigua.js?v={{$assetVer}}"></script>
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
  #vpscs-landing { display: none; min-height: 100vh; color: #f2f5f2; background: #0b100e; font-family: Inter, system-ui, sans-serif; color-scheme: dark; }
  body.vpscs-landing-mode #vpscs-landing { display: block; }
  body.vpscs-landing-mode #app { display: none; }
  .vl-wrap { width: min(1120px, calc(100% - 40px)); margin: auto; }
  .vl-top { position: sticky; top: 0; z-index: 20; border-bottom: 1px solid #26322c; background: rgba(11,16,14,.9); backdrop-filter: blur(14px); }
  .vl-top-in { height: 68px; display: flex; align-items: center; justify-content: space-between; }
  .vl-brand { display: flex; align-items: center; gap: 10px; color: #f4f7f4; font-size: 20px; font-weight: 800; text-decoration: none; }
  .vl-mark { width: 28px; height: 28px; border: 7px solid #d9f8e5; border-right-color: #48b77a; border-radius: 50%; transform: rotate(-20deg); }
  .vl-actions, .vl-cta { display: flex; gap: 12px; align-items: center; }
  .vl-link { color: #d9e7df; text-decoration: none; font-weight: 600; }
  .vl-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 22px; border-radius: 10px; color: #06100a; background: #4fbd80; text-decoration: none; font-weight: 800; transition: transform .2s, background .2s; }
  .vl-btn:hover { transform: translateY(-1px); background: #69d297; }
  .vl-btn-soft { color: #e5f4eb; background: #17231d; border: 1px solid #31433a; }
  .vl-hero { padding: 92px 0 78px; background: radial-gradient(circle at 75% 20%, #183526 0, transparent 35%), #0b100e; }
  .vl-hero-in { display: grid; grid-template-columns: 1.1fr .9fr; gap: 72px; align-items: center; }
  .vl-eyebrow { display: inline-flex; gap: 8px; align-items: center; padding: 8px 12px; border-radius: 999px; color: #9ce4bb; background: #173426; font-weight: 700; }
  .vl-dot { width: 8px; height: 8px; border-radius: 50%; background: #4fbd80; }
  .vl-title { margin: 22px 0 18px; font-size: clamp(42px, 7vw, 76px); line-height: 1.02; letter-spacing: -.05em; }
  .vl-lead { max-width: 620px; color: #a8b9af; font-size: 18px; line-height: 1.8; }
  .vl-cta { margin-top: 30px; }
  .vl-platforms { margin-top: 18px; color: #82988c; }
  .vl-phone { width: min(330px, 100%); margin: auto; padding: 18px; border: 10px solid #243128; border-radius: 38px; background: #121a16; box-shadow: 0 30px 80px rgba(0,0,0,.38); }
  .vl-phone-top { display: flex; gap: 8px; align-items: center; font-weight: 800; }
  .vl-mini-card { margin-top: 34px; padding: 22px; border-radius: 20px; color: #fff; background: linear-gradient(145deg,#142018,#245b3b); }
  .vl-mini-label { color: #a9d9bd; font-size: 13px; }
  .vl-mini-plan { min-height: 48px; font-weight: 750; }
  .vl-bar { height: 8px; border-radius: 20px; background: rgba(255,255,255,.2); overflow: hidden; }
  .vl-bar span { display: block; width: 36%; height: 100%; background: #65cf91; }
  .vl-mini-button { margin-top: 22px; padding: 11px; border-radius: 10px; color: #06100a; text-align: center; background: #4fbd80; }
  .vl-section { padding: 82px 0; }
  .vl-soft { background: #141c18; }
  .vl-center { text-align: center; }
  .vl-h2 { margin: 0 0 14px; font-size: clamp(30px,4vw,44px); letter-spacing: -.035em; }
  .vl-sub { margin: 0 auto 38px; max-width: 660px; color: #9aada2; font-size: 17px; line-height: 1.7; }
  .vl-features { display: grid; grid-template-columns: repeat(4,1fr); gap: 18px; padding: 0; list-style: none; }
  .vl-plans { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; padding: 0; list-style: none; }
  .vl-feature, .vl-plan, .vl-step, .vl-region { padding: 26px; border: 1px solid #29362f; border-radius: 18px; background: #121916; }
  .vl-plan { min-height: 430px; display: flex; flex-direction: column; border-color: #00a878; background: #17191d; box-shadow: 0 18px 45px rgba(0,0,0,.22); }
  .vl-plan:nth-child(3n+2) { border-color: #ff9d00; }
  .vl-plan:nth-child(3n) { border-color: #9b20e8; }
  .vl-feature-icon { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 12px; color: #9ce4bb; background: #193526; font-size: 22px; }
  .vl-feature h3, .vl-plan h3 { margin: 18px 0 10px; font-size: 20px; }
  .vl-feature p, .vl-plan-desc { color: #9aada2; line-height: 1.7; white-space: pre-line; }
  .vl-kind { display: inline-flex; padding: 5px 9px; border-radius: 999px; color: #9ce4bb; background: #193526; font-size: 13px; font-weight: 700; }
  .vl-price { margin: 22px 0 5px; font-size: 30px; font-weight: 850; }
  .vl-price-note { min-height: 44px; color: #91a399; font-size: 14px; line-height: 1.5; }
  .vl-plan .vl-btn { width: 100%; box-sizing: border-box; }
  .vl-plan-benefits { min-height: 150px; margin: 18px 0; padding: 0; color: #c4cbd2; list-style: none; line-height: 1.8; }
  .vl-plan .vl-price-note { margin-top: auto; }
  .vl-plan .vl-btn { margin-top: 16px; min-height: 54px; border-radius: 14px; }
  .vl-plan:nth-child(3n+2) .vl-btn { background: #ff9d00; }
  .vl-plan:nth-child(3n) .vl-btn { background: #8e17d8; color: #fff; }
  .vl-empty { grid-column: 1/-1; padding: 30px; text-align: center; color: #93a49a; }
  .vl-final { padding: 58px; border-radius: 24px; color: #06100a; text-align: center; background: #52ba7d; }
  .vl-final .vl-btn { color: #dff8e9; background: #07100c; }
  .vl-foot { padding: 30px 0; border-top: 1px solid #26322c; color: #82958a; }
  .vl-foot-in { display: grid; grid-template-columns: 1fr auto 1fr; gap: 18px; align-items: center; }
  .vl-foot-in > :last-child { justify-self: end; }
  .vl-version { padding: 5px 10px; border: 1px solid #2b3a32; border-radius: 999px; color: #9fb2a7; background: #111814; font-size: 12px; letter-spacing: .04em; }
  .vl-steps { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; padding: 0; list-style: none; }
  .vl-step-n { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 13px; color: #07100b; background: #4fbd80; font-weight: 900; }
  .vl-step h3 { margin: 18px 0 8px; }
  .vl-step p { margin: 0; color: #9aada2; line-height: 1.7; }
  .vl-regions-wrap { display: grid; grid-template-columns: .75fr 1.25fr; gap: 70px; align-items: center; }
  .vl-regions { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; padding: 0; list-style: none; }
  .vl-region { padding: 16px; display: grid; grid-template-columns: 48px 1fr; gap: 4px 12px; }
  .vl-cc { grid-row: 1/3; display: grid; place-items: center; border-radius: 12px; color: #93e1b3; background: #193526; font-weight: 900; }
  .vl-region-name { font-weight: 800; }
  .vl-region-count { color: #91a399; font-size: 14px; }
  .vl-plan-filter { width: max-content; max-width: 100%; margin: 0 auto 32px; padding: 6px; display: flex; gap: 6px; border: 1px solid #3a2b35; border-radius: 999px; background: #1b1118; }
  .vl-filter-btn { min-height: 42px; padding: 0 22px; border: 0; border-radius: 999px; color: #d7cbd3; background: transparent; cursor: pointer; font-size: 16px; }
  .vl-filter-btn.is-active { color: #fff; background: #e64e91; box-shadow: 0 8px 24px rgba(230,78,145,.3); }
  /* The native shelf is replaced by the shared theme shelf.  Keep several
   * selectors here: the app occasionally re-renders the grid and some builds
   * add a wrapper that would otherwise restore the original grid. */
  body.vpscs-shelf-mode .card-container,
  body.vpscs-shelf-mode .card-container > .card-item,
  html body.vpscs-shelf-mode .card-container,
  body.vpscs-shelf-mode [data-vpscs-native-grid] { display: none !important; }
  .vpscs-plan-shelf .vl-plan { cursor: pointer; transition: transform .2s, border-color .2s; }
  .vpscs-plan-shelf .vl-plan:hover { transform: translateY(-2px); }
  .vpscs-plan-shelf .vl-btn { width: 100%; border: 0; cursor: pointer; font-family: inherit; font-size: 16px; }
  .vl-plan-benefits .is-off { color: #6b7280; }

  /* 用户中心「购买订阅」：与着陆页同一套居中版式，周期 / 一次性分成上下两组 */
  .vpscs-shelf { width: min(1120px, 100%); margin: 0 auto; padding: 6px 16px 46px; box-sizing: border-box; }
  .vpscs-shelf-head { max-width: 720px; margin: 10px auto 30px; text-align: center; }
  .vpscs-shelf-head .vl-h2 { margin: 0 0 10px; }
  .vpscs-shelf-head .vl-sub { margin: 0 auto 22px; }
  .vpscs-shelf-head .vl-plan-filter { margin-bottom: 0; }
  .vpscs-shelf-group { margin: 0 0 40px; }
  .vpscs-shelf-group:last-child { margin-bottom: 0; }
  .vpscs-shelf-group-title { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin: 0 2px 16px; color: #eef4f0; font-size: 20px; font-weight: 800; letter-spacing: -.02em; }
  .vpscs-shelf-group-title > span { color: #82958a; font-size: 13px; font-weight: 500; letter-spacing: 0; }
  .vpscs-shelf-group .vl-plans { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .vpscs-shelf .vl-plan { min-height: 0; }
  .vpscs-shelf .vl-plan-benefits { min-height: 120px; }
  body.vpscs-plan-page .vpscs-native-heading,
  body.vpscs-plan-page #app .vpscs-native-filter { display: none !important; }
  @media (max-width: 1000px) { .vpscs-shelf-group .vl-plans { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 680px) {
    .vpscs-shelf { padding: 4px 14px 34px; }
    .vpscs-shelf-group .vl-plans { grid-template-columns: 1fr; }
    .vpscs-shelf-group-title { font-size: 17px; }
  }

  /* 套餐详情：付款周期提到页面最顶部整行横向排列，勾选态用圆形对号表达 */
  .vpscs-period-top { margin: 18px 0 0 !important; }
  .vpscs-period-top .n-card__content { display: grid !important; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; padding: 14px 16px !important; }
  .vpscs-period-top .n-card__content hr { display: none !important; }
  .vpscs-period-top .n-card__content > div { min-width: 0; box-sizing: border-box; }
  .vpscs-period-row { width: 100%; box-sizing: border-box; display: grid !important; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 9px; padding: 12px 13px !important; border-radius: 12px !important; border-width: 1px !important; border-color: #2c3b33 !important; background: #141b17; white-space: nowrap; transition: background .18s ease, border-color .18s ease; }
  .vpscs-period-row::before { content: ''; width: 18px; height: 18px; box-sizing: border-box; border-radius: 50%; border: 1.5px solid #46584d; background: transparent; }
  .vpscs-period-row[class~="border-primary"] { background: #16241c !important; border-color: #4fbd80 !important; }
  .vpscs-period-row[class~="border-primary"]::before { content: '✓'; display: grid; place-items: center; color: #06100a; border-color: #4fbd80; background: #4fbd80; font-size: 12px; font-weight: 900; line-height: 1; }
  .vpscs-period-row > div { min-width: 0; white-space: nowrap; }
  @media (max-width: 680px) { .vpscs-period-top .n-card__content { grid-template-columns: 1fr; } }
  @media (max-width: 820px) {
    .vl-hero-in { grid-template-columns: 1fr; text-align: center; }
    .vl-lead { margin-inline: auto; }
    .vl-cta { justify-content: center; }
    .vl-features, .vl-plans, .vl-steps, .vl-regions { grid-template-columns: 1fr; }
    .vl-regions-wrap { grid-template-columns: 1fr; gap: 28px; }
    .vl-phone { display: none; }
    .vl-hero { padding: 64px 0; }
    .vl-section { padding: 58px 0; }
    .vl-final { padding: 38px 22px; }
    .vl-foot-in { grid-template-columns: 1fr; text-align: center; }
    .vl-foot-in > :last-child { justify-self: center; }
  }
</style>

<body>

  <div id="vpscs-landing">
    <header class="vl-top"><div class="vl-wrap vl-top-in">
      <a class="vl-brand" href="/"><span class="vl-mark"></span><span>{{ $title }}</span></a>
      <nav class="vl-actions" data-landing-auth><a class="vl-link vl-enter-app" href="#/login">登录</a><a class="vl-btn vl-enter-app" href="#/register">注册</a></nav>
    </div></header>
    <main>
      <section class="vl-hero"><div class="vl-wrap vl-hero-in">
        <div><p class="vl-eyebrow"><span class="vl-dot"></span>稳定、安全、简单易用</p><h1 class="vl-title">{{ $title }}</h1><p class="vl-lead">让复杂的线路配置留在后台，为您提供清晰的套餐选择与便捷的全平台连接体验。</p><p class="vl-cta" data-landing-auth><a class="vl-btn vl-enter-app" href="#/register">立即注册</a><a class="vl-btn vl-btn-soft vl-enter-app" href="#/login">登录</a></p><p class="vl-platforms">支持 iPhone、安卓、Mac 与 Windows</p></div>
        <div class="vl-phone"><p class="vl-phone-top"><span class="vl-mark"></span>{{ $title }}</p><div class="vl-mini-card"><p class="vl-mini-label">我的套餐</p><p class="vl-mini-plan">高速稳定 · 多平台通用</p><p class="vl-bar"><span></span></p><p>本期流量使用情况</p><p class="vl-mini-button">复制订阅链接</p></div></div>
      </div></section>
      <section class="vl-section"><div class="vl-wrap"><h2 class="vl-h2 vl-center">为速度与稳定而做</h2><p class="vl-sub vl-center">简单选择套餐，登录后即可获取订阅并开始使用。</p><ul class="vl-features">
        <li class="vl-feature"><span class="vl-feature-icon">⚡</span><h3>高速稳定</h3><p>多线路智能调度，满足日常浏览、视频与工作需求。</p></li>
        <li class="vl-feature"><span class="vl-feature-icon">⌁</span><h3>一键添加</h3><p>复制订阅链接，在客户端添加一次，后续打开即可连接。</p></li>
        <li class="vl-feature"><span class="vl-feature-icon">▣</span><h3>全平台通用</h3><p>支持手机和电脑，一个账号管理套餐、流量与设备。</p></li>
        <li class="vl-feature"><span class="vl-feature-icon">▢</span><h3>加密传输</h3><p>连接过程加密，在公共网络环境下使用更加安心。</p></li>
      </ul></div></section>
      <section class="vl-section vl-soft"><div class="vl-wrap"><h2 class="vl-h2 vl-center">三步开始使用</h2><p class="vl-sub vl-center">从注册到连接，只需要简单三步。</p><ol class="vl-steps">
        <li class="vl-step"><span class="vl-step-n">1</span><h3>注册账号</h3><p>创建账号并进入用户中心。</p></li>
        <li class="vl-step"><span class="vl-step-n">2</span><h3>选择套餐</h3><p>按时间或一次性流量，选择适合自己的套餐。</p></li>
        <li class="vl-step"><span class="vl-step-n">3</span><h3>添加到 App</h3><p>复制订阅链接并添加到客户端即可使用。</p></li>
      </ol></div></section>
      <section class="vl-section"><div class="vl-wrap vl-regions-wrap"><div><h2 id="vl-region-title" class="vl-h2">覆盖多个地区</h2><p class="vl-sub" style="margin:0">节点按地区分布，后台可以随时增加、修改或删除展示地区。</p></div><ul id="vl-region-list" class="vl-regions"></ul></div></section>
      <section class="vl-section vl-soft" id="plans"><div class="vl-wrap"><h2 class="vl-h2 vl-center">套餐与价格</h2><p class="vl-sub vl-center">以下内容实时读取后台已上架商品，修改套餐后首页自动同步。</p><div class="vl-plan-filter" role="group" aria-label="套餐类型"><button class="vl-filter-btn is-active" data-plan-filter="all">全部</button><button class="vl-filter-btn" data-plan-filter="periodic">周期性</button><button class="vl-filter-btn" data-plan-filter="onetime">一次性</button></div><ul id="vl-plan-list" class="vl-plans"><li class="vl-empty">正在加载套餐…</li></ul></div></section>
      <section class="vl-section"><div class="vl-wrap"><div class="vl-final"><h2 class="vl-h2">现在开始</h2><p>注册账号、选择套餐，几分钟即可完成配置。</p><p class="vl-cta" data-landing-auth style="justify-content:center"><a class="vl-btn vl-enter-app" href="#/register">立即注册</a><a class="vl-link vl-enter-app" style="color:#fff" href="#/login">已有账号，去登录</a></p></div></div></section>
    </main>
    <footer class="vl-foot"><div class="vl-wrap vl-foot-in"><span>© {{ $title }}</span><span class="vl-version">VPSCS v2026.11.28.1</span><span data-landing-auth><a class="vl-link vl-enter-app" href="#/login">登录</a> · <a class="vl-link vl-enter-app" href="#/register">注册</a></span></div></footer>
  </div>

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
  <script>
    (function () {
      var appLoaded = false;
      function loadUserApp() {
        if (appLoaded) return;
        appLoaded = true;
        var script = document.createElement('script');
        script.type = 'module';
        script.crossOrigin = 'anonymous';
        script.src = '/theme/{{$theme}}/assets/umi.js?v={{$assetVer}}';
        document.head.appendChild(script);
      }
      // 续费时要把 subscription_id 带进下单请求，否则后端会把续费判成新购。
      // 应用内部下单走 axios(XHR) + x-www-form-urlencoded，fetch 与 XHR 两条通道都要挂。
      var RENEW_URL = /\/api\/v1\/user\/order\/save(?:\?|$)/;
      var RENEW_KEY = 'vpscs_renew_subscription_id';
      function readRenew() {
        try { return JSON.parse(sessionStorage.getItem(RENEW_KEY) || 'null'); } catch (_) { return null; }
      }
      function clearRenew() { sessionStorage.removeItem(RENEW_KEY); }
      function withRenewId(body) {
        var renew = readRenew();
        if (!renew || !renew.id) return body;
        // 只有仍然停在这张订阅对应的套餐详情页时才注入，避免误绑到别的套餐。
        if (renew.plan && !new RegExp('^#/plan/' + renew.plan + '(?:[?/]|$)').test(location.hash)) {
          clearRenew();
          return body;
        }
        clearRenew();
        var text = typeof body === 'string' ? body : '';
        if (text.trim().charAt(0) === '{') {
          try {
            var payload = JSON.parse(text);
            payload.subscription_id = Number(renew.id);
            return JSON.stringify(payload);
          } catch (_) {}
        }
        return (text ? text + '&' : '') + 'subscription_id=' + encodeURIComponent(String(renew.id));
      }
      var nativeFetch = window.fetch.bind(window);
      window.fetch = function (input, init) {
        var url = typeof input === 'string' ? input : input.url;
        if (RENEW_URL.test(url) && readRenew() && init && typeof init.body === 'string') {
          init = Object.assign({}, init, { body: withRenewId(init.body) });
        }
        return nativeFetch(input, init);
      };
      var nativeOpen = XMLHttpRequest.prototype.open;
      var nativeSend = XMLHttpRequest.prototype.send;
      XMLHttpRequest.prototype.open = function (method, url) {
        this.__vpscsUrl = String(url || '');
        return nativeOpen.apply(this, arguments);
      };
      XMLHttpRequest.prototype.send = function (body) {
        if (RENEW_URL.test(this.__vpscsUrl || '') && readRenew() && typeof body === 'string') {
          body = withRenewId(body);
        }
        return nativeSend.call(this, body);
      };

      function inLanding() { return !location.hash || location.hash === '#' || location.hash === '#/'; }
      var landingRequested = inLanding();
      function syncView() {
        document.body.classList.toggle('vpscs-landing-mode', landingRequested || inLanding());
        if (landingRequested && location.hash && !inLanding()) history.replaceState(null, '', location.pathname + location.search);
      }
      syncView();
      if (!landingRequested) loadUserApp();
      addEventListener('hashchange', syncView);
      document.querySelectorAll('.vl-enter-app').forEach(function (link) { link.addEventListener('click', function () { landingRequested = false; document.body.classList.remove('vpscs-landing-mode'); loadUserApp(); }); });

      var periods = [
        ['month_price', '月'], ['quarter_price', '季'], ['half_year_price', '半年'],
        ['year_price', '年'], ['two_year_price', '两年'], ['three_year_price', '三年']
      ];
      var landingPlans = [];
      var planFilter = 'all';
      var regionSource = @json($theme_config['landing_regions'] ?? '');
      var regions = String(regionSource || '').split(/\r?\n/).map(function (line) {
        var parts = line.split('|').map(function (part) { return part.trim(); });
        return { code: parts[0] || '', name: parts[1] || '', count: parts[2] || '' };
      }).filter(function (region) { return region.code && region.name; });
      document.getElementById('vl-region-title').textContent = '覆盖 ' + regions.length + ' 个地区';
      document.getElementById('vl-region-list').innerHTML = regions.map(function (region) {
        return '<li class="vl-region"><span class="vl-cc">' + escapeHtml(region.code) + '</span><span class="vl-region-name">' + escapeHtml(region.name) + '</span><span class="vl-region-count">' + escapeHtml(region.count ? region.count + ' 个节点' : '已覆盖') + '</span></li>';
      }).join('');

      // 商品卡的唯一模板：着陆页与用户中心共用同一份，字段/排版不会再分叉。
      function planBenefits(content) {
        var raw = String(content == null ? '' : content);
        var parsed = null;
        try { parsed = JSON.parse(raw); } catch (_) {}
        if (Array.isArray(parsed)) {
          return parsed.slice(0, 6).map(function (item) {
            var ok = item && item.support !== false;
            return '<li class="' + (ok ? '' : 'is-off') + '">' + (ok ? '✓ ' : '✗ ') + escapeHtml((item && item.feature) || '') + '</li>';
          }).join('');
        }
        var text = raw.replace(/<br\s*\/?>/gi, '\n').replace(/<\/(p|div|li|h[1-6])>/gi, '\n').replace(/<[^>]+>/g, '');
        return text.split(/\r?\n/).map(function (line) { return line.trim(); }).filter(Boolean).slice(0, 6)
          .map(function (line) { return '<li>✓ ' + escapeHtml(line) + '</li>'; }).join('');
      }

      function planCardHtml(plan, action) {
        var hasPeriodic = periods.some(function (p) { return Number(plan[p[0]] || 0) > 0; });
        var one = !hasPeriodic && Number(plan.onetime_price || 0) > 0;
        var selected = one ? ['onetime_price', '一次性'] : periods.find(function (p) { return Number(plan[p[0]] || 0) > 0; });
        if (!selected) selected = ['onetime_price', '一次性'];
        var price = Number(plan[selected[0]] || 0) / 100;
        var suffix = one ? '' : ' / ' + selected[1];
        var notes = [];
        if (!one) {
          periods.forEach(function (p) {
            if (p[0] !== selected[0] && Number(plan[p[0]] || 0) > 0) {
              notes.push(p[1] + '付 ¥' + (Number(plan[p[0]]) / 100).toFixed(2));
            }
          });
        }
        // 后台「流量包/重置包」价格也要出现在卡片上，价格才和套餐管理一致。
        if (Number(plan.reset_price || 0) > 0) notes.push('流量包 ¥' + (Number(plan.reset_price) / 100).toFixed(2));
        var extra = notes.slice(0, 3).join(' · ') || (one ? '一次购买，长期有效' : '');
        return '<li class="vl-plan" data-plan-id="' + escapeHtml(String(plan.id || '')) + '">'
          + '<span class="vl-kind">' + (one ? '一次性套餐' : '周期套餐') + '</span>'
          + '<h3>' + escapeHtml(plan.name || '') + '</h3>'
          + '<p class="vl-price">¥' + price.toFixed(2) + suffix + '</p>'
          + '<ul class="vl-plan-benefits">' + planBenefits(plan.content) + '</ul>'
          + '<p class="vl-price-note">' + escapeHtml(extra) + '</p>'
          + action + '</li>';
      }

      function filterPlans(plans, mode) {
        return plans.filter(function (plan) {
          var one = plan.onetime_price != null && Number(plan.onetime_price) > 0;
          return mode === 'all' || (mode === 'onetime' ? one : !one);
        });
      }

      function renderPlans() {
        var list = document.getElementById('vl-plan-list');
        var plans = filterPlans(landingPlans, planFilter);
        if (!plans.length) { list.innerHTML = '<li class="vl-empty">该分类暂无在售套餐</li>'; return; }
        list.innerHTML = plans.map(function (plan) {
          return planCardHtml(plan, '<a class="vl-btn" href="#/register">订阅</a>');
        }).join('');
      }
      // 卡片是异步渲染的，点击必须委托，否则按钮拿到的是没绑事件的死链。
      document.getElementById('vl-plan-list').addEventListener('click', function (event) {
        var link = event.target.closest('a.vl-btn');
        if (!link) return;
        event.preventDefault();
        landingRequested = false;
        document.body.classList.remove('vpscs-landing-mode');
        loadUserApp();
        location.hash = storedAccessToken() ? '#/plan' : '#/register';
      });
      document.querySelectorAll('.vl-filter-btn').forEach(function (button) { button.addEventListener('click', function () { planFilter = button.dataset.planFilter; document.querySelectorAll('.vl-filter-btn').forEach(function (item) { item.classList.toggle('is-active', item === button); }); renderPlans(); }); });
      fetch('/api/v1/guest/plan/fetch').then(function (r) { return r.json(); }).then(function (payload) {
        landingPlans = payload.data || [];
        renderPlans();
      }).catch(function () { document.getElementById('vl-plan-list').innerHTML = '<li class="vl-empty">套餐加载失败，请稍后刷新</li>'; });

      function escapeHtml(value) { var div = document.createElement('div'); div.textContent = value; return div.innerHTML; }

      // Xboard 的 storage 封装 getKey() 会 toUpperCase()，实际 key 是全大写的
      // VUE_NAIVE_ACCESS_TOKEN，值形如 {"value":"<token>","time":...,"expire":...}。
      // 按驼峰 key 去读永远是 null，请求就不带 Authorization，接口一律 403。
      function storedAccessToken() {
        var raw = '';
        try {
          for (var i = 0; i < localStorage.length; i += 1) {
            var key = localStorage.key(i) || '';
            if (key.toUpperCase() === 'VUE_NAIVE_ACCESS_TOKEN') { raw = localStorage.getItem(key) || ''; break; }
          }
        } catch (_) {}
        if (!raw) raw = localStorage.getItem('access_token') || localStorage.getItem('Vue_Naive_access_token') || '';
        var token = raw;
        try {
          var saved = JSON.parse(raw);
          if (saved && typeof saved === 'object') {
            if (saved.expire && Number(saved.expire) <= Date.now()) return '';
            token = typeof saved.value === 'string' ? saved.value : '';
          }
        } catch (_) {}
        return typeof token === 'string' ? token : '';
      }
      function authHeaders() {
        var token = storedAccessToken();
        return token ? { Authorization: token } : {};
      }
      function enterApp(event) {
        if (event) event.preventDefault();
        landingRequested = false;
        document.body.classList.remove('vpscs-landing-mode');
        loadUserApp();
        location.hash = '#/dashboard';
      }
      function syncLandingAuth() {
        if (!storedAccessToken()) return;
        document.querySelectorAll('[data-landing-auth]').forEach(function (node) {
          node.innerHTML = '<a class="vl-btn vl-dashboard-link" href="#/dashboard">进入后台</a>';
          node.querySelector('.vl-dashboard-link').addEventListener('click', enterApp);
        });
      }
      syncLandingAuth();

      // The app's own labels get a light touch-up (detail / close actions), while
      // the plan shelf is re-painted on every relevant DOM change.
      new MutationObserver(function () {
        document.querySelectorAll('label,button,span').forEach(function (node) {
          var label = node.childElementCount === 0 ? node.textContent.trim() : '';
          var action = node.closest('button,a');
          if (action && ['查看', '查看详情'].includes(label)) action.classList.add('vpscs-action-view');
          if (action && ['关闭', '取消'].includes(label)) action.classList.add('vpscs-action-danger');
        });
        syncPlanShelf();
        enhancePlanPeriod();
        renderSubscriptionList();
        pruneShortcutRows();
      }).observe(document.getElementById('app'), { childList: true, subtree: true });

      // 用户中心「购买订阅」：隐藏应用原生卡片，用与着陆页完全相同的模板重绘。
      var shelfPlans = null;
      var shelfLoading = false;
      var shelfFilter = 'all';

      function planShelfMode() { return /^#\/plan\/?$/.test(location.hash); }
      function planDetailMode() { return /^#\/plan\/\d+/.test(location.hash); }

      // 隐藏应用自带的标题与筛选器：版式改由与着陆页一致的区块接管。
      function tagPlanChrome() {
        if (!planShelfMode()) return;
        Array.prototype.slice.call(document.querySelectorAll('#app h2')).forEach(function (heading) {
          if (heading.classList.contains('vl-h2') || heading.closest('.vpscs-shelf')) return;
          if (heading.textContent.replace(/\s+/g, '').indexOf('选择最适合') === 0) heading.classList.add('vpscs-native-heading');
        });
        // 原生筛选是 Naive UI 的 radio 组（全部 / 按周期 / 按流量），按标签定位后整组隐藏。
        Array.prototype.slice.call(document.querySelectorAll('#app label, #app .n-radio__label')).forEach(function (label) {
          var text = label.textContent.replace(/\s+/g, '');
          if (text !== '按周期' && text !== '按流量') return;
          var group = label.closest('.n-radio-group') || label.closest('[role="radiogroup"]');
          if (group) group.classList.add('vpscs-native-filter');
        });
      }

      // The app re-renders its own grid on every filter/sort change, so the
      // native shelf is hidden with an inline !important style: relying on a
      // single class rule was not enough after a client-side route change.
      function hideNativePlanGrid() {
        // Vue may teleport the plan page outside #app, so do not scope this to
        // one root: every native plan grid on the page is ours to replace.
        Array.prototype.slice.call(document.querySelectorAll('.card-container')).forEach(function (grid) {
          grid.setAttribute('data-vpscs-native-grid', '1');
          grid.style.setProperty('display', 'none', 'important');
        });
      }

      function restoreNativePlanGrid() {
        Array.prototype.slice.call(document.querySelectorAll('[data-vpscs-native-grid]')).forEach(function (grid) {
          grid.style.removeProperty('display');
          grid.removeAttribute('data-vpscs-native-grid');
        });
      }

      var shelfPaintedKey = '';
      function shelfHeadHtml() {
        var options = [['all', '全部'], ['periodic', '周期性'], ['onetime', '一次性']];
        return '<div class="vpscs-shelf-head">'
          + '<h2 class="vl-h2">选择最适合您的计划</h2>'
          + '<p class="vl-sub">所有套餐与后台商品实时同步，周期套餐与一次性流量包分开选择。</p>'
          + '<div class="vl-plan-filter" role="group" aria-label="套餐类型">'
          + options.map(function (option) {
            return '<button type="button" class="vl-filter-btn' + (shelfFilter === option[0] ? ' is-active' : '') + '" data-shelf-filter="' + option[0] + '">' + option[1] + '</button>';
          }).join('')
          + '</div></div>';
      }

      function shelfSectionHtml(kind, plans) {
        if (!plans.length) return '';
        var label = kind === 'onetime' ? '一次性流量套餐' : '周期套餐';
        return '<section class="vpscs-shelf-group" data-kind="' + kind + '">'
          + '<h3 class="vpscs-shelf-group-title">' + label + '<span>' + plans.length + ' 个在售</span></h3>'
          + '<ul class="vl-plans">' + plans.map(function (plan) {
            return planCardHtml(plan, '<button type="button" class="vl-btn" data-buy="' + escapeHtml(String(plan.id || '')) + '">立即订阅</button>');
          }).join('') + '</ul></section>';
      }

      function paintPlanShelf() {
        var shelf = document.getElementById('vpscs-plan-shelf');
        if (!shelf || !shelfPlans) return;
        var key = shelfFilter + '|' + JSON.stringify(shelfPlans);
        if (key === shelfPaintedKey) return;
        shelfPaintedKey = key;
        var periodic = shelfFilter === 'onetime' ? [] : filterPlans(shelfPlans, 'periodic');
        var onetime = shelfFilter === 'periodic' ? [] : filterPlans(shelfPlans, 'onetime');
        var html = shelfHeadHtml() + shelfSectionHtml('periodic', periodic) + shelfSectionHtml('onetime', onetime);
        if (!periodic.length && !onetime.length) html += '<p class="vl-empty">该分类暂无在售套餐</p>';
        shelf.innerHTML = html;
        hideNativePlanGrid();
        tagPlanChrome();
      }

      function fetchShelfPlans(shelf) {
        if (shelfLoading) return;
        shelfLoading = true;
        shelf.innerHTML = shelfHeadHtml() + '<p class="vl-empty">正在加载套餐…</p>';
        nativeFetch('/api/v1/user/plan/fetch', { headers: authHeaders(), credentials: 'same-origin', cache: 'no-store' })
          .then(function (r) { if (!r.ok) throw new Error(r.status === 403 ? '登录已过期，请重新登录' : '套餐加载失败（' + r.status + '）'); return r.json(); })
          .then(function (payload) { shelfPlans = payload.data || []; shelfPaintedKey = ''; paintPlanShelf(); })
          .catch(function (error) { shelf.innerHTML = shelfHeadHtml() + '<p class="vl-empty">' + escapeHtml(error.message || '套餐加载失败') + '</p>'; })
          .finally(function () { shelfLoading = false; });
      }

      function mountPlanShelf() {
        if (!planShelfMode()) return;
        var shelf = document.getElementById('vpscs-plan-shelf');
        if (!shelf) {
          var grids = Array.prototype.slice.call(document.querySelectorAll('.card-container'));
          var grid = grids.filter(function (item) { return item.getClientRects().length > 0; })[0] || grids[0];
          if (!grid) return;
          shelf = document.createElement('div');
          shelf.id = 'vpscs-plan-shelf';
          shelf.className = 'vpscs-shelf';
          grid.parentNode.insertBefore(shelf, grid.nextSibling);
        }
        // Keep both guards: the class for CSS, the inline style for safety.
        document.body.classList.add('vpscs-shelf-mode');
        hideNativePlanGrid();
        tagPlanChrome();
        if (shelfPlans) { paintPlanShelf(); return; }
        fetchShelfPlans(shelf);
      }

      function syncPlanShelf() {
        var onPlan = planShelfMode();
        document.body.classList.toggle('vpscs-plan-page', onPlan);
        if (!onPlan) {
          var stale = document.getElementById('vpscs-plan-shelf');
          if (stale) {
            stale.remove();
            // Force a re-fetch next time: an administrator may have edited a
            // product in the meantime, and the card must follow that change.
            shelfPlans = null;
            shelfPaintedKey = '';
            shelfLoading = false;
          }
          document.body.classList.remove('vpscs-shelf-mode');
          restoreNativePlanGrid();
          return;
        }
        mountPlanShelf();
      }

      document.addEventListener('click', function (event) {
        if (!planShelfMode()) return;
        var filter = event.target.closest('#vpscs-plan-shelf [data-shelf-filter]');
        if (filter) {
          event.preventDefault();
          shelfFilter = filter.getAttribute('data-shelf-filter');
          shelfPaintedKey = '';
          paintPlanShelf();
          return;
        }
        var buy = event.target.closest('#vpscs-plan-shelf [data-buy]');
        var card = event.target.closest('#vpscs-plan-shelf .vl-plan');
        if (buy) { event.preventDefault(); location.hash = '#/plan/' + buy.getAttribute('data-buy'); return; }
        if (card) { location.hash = '#/plan/' + (card.getAttribute('data-plan-id') || ''); return; }
      }, true);
      addEventListener('hashchange', function () { setTimeout(syncPlanShelf, 0); });
      // Vue re-creates the native grid when the segmented filter changes; hide
      // it immediately instead of waiting for the 900ms safety tick.
      (function () {
        if (!window.MutationObserver) return;
        var queued = false;
        new MutationObserver(function () {
          if (queued) return;
          queued = true;
          setTimeout(function () {
            queued = false;
            if (!planShelfMode()) return;
            if (document.getElementById('vpscs-plan-shelf')) { hideNativePlanGrid(); tagPlanChrome(); }
          }, 0);
        }).observe(document.body, { childList: true, subtree: true });
      })();

      // 套餐详情：把「付款周期」提到两栏布局上方，整行横向排列，用对号表示已选中。
      function enhancePlanPeriod() {
        if (!planDetailMode()) {
          document.body.classList.remove('vpscs-period-moved');
          return;
        }
        var cards = Array.prototype.slice.call(document.querySelectorAll('#app .n-card'));
        var periodCard = null;
        cards.forEach(function (card) {
          var header = card.querySelector('.n-card-header__main, .n-card-header');
          if (header && header.textContent.replace(/\s+/g, '') === '付款周期') periodCard = card;
        });
        if (!periodCard) return;
        if (!periodCard.classList.contains('vpscs-period-top')) {
          var leftColumn = periodCard.parentElement;
          var layout = leftColumn && leftColumn.parentElement;
          // 只在「左侧套餐信息 + 右侧订单栏」的两栏布局里上移，避免搬乱别的页面。
          if (layout && /grid-cols-/.test(String(layout.className || ''))) {
            layout.parentNode.insertBefore(periodCard, layout);
            periodCard.classList.add('vpscs-period-top');
            document.body.classList.add('vpscs-period-moved');
          }
        }
        // Rows are re-rendered on every period change, so tag them each pass.
        Array.prototype.slice.call(periodCard.querySelectorAll('.n-card__content > div')).forEach(function (wrapper) {
          var row = wrapper.firstElementChild || wrapper;
          row.classList.add('vpscs-period-row');
        });
      }

      var subscriptionRenderBusy = false;
      var subscriptionItems = [];
      function renderSubscriptionList() {
        if (!/dashboard/i.test(location.hash) || subscriptionRenderBusy || document.getElementById('vpscs-subscription-list')) return;
        var card = Array.from(document.querySelectorAll('#app .n-card')).find(function (node) { return node.textContent.includes('我的订阅'); });
        var content = card && card.querySelector('.n-card__content');
        if (!content || content.dataset.vpscsSubscriptionManaged === '1') return;
        content.dataset.vpscsSubscriptionManaged = '1';
        content.classList.add('vpscs-subscription-host');
        var loading = document.createElement('div');
        loading.className = 'vpscs-subscription-loading';
        loading.textContent = '正在加载订阅卡片…';
        content.replaceChildren(loading);
        subscriptionRenderBusy = true;
        nativeFetch('/api/v1/user/getSubscriptions', { headers: authHeaders(), credentials: 'same-origin' }).then(function (r) {
          if (!r.ok) throw new Error(r.status === 403 ? '登录已过期，请重新登录' : '订阅接口请求失败（' + r.status + '）');
          return r.json();
        }).then(function (payload) {
          if (payload.code !== 0 && payload.code !== 200 && !Array.isArray(payload.data)) throw new Error(payload.message || '订阅加载失败');
          var items = payload.data || [];
          if (!Array.isArray(items) || !items.length) throw new Error(payload.message || '暂时没有有效订阅');
          subscriptionItems = items;
          var list = document.createElement('div'); list.id = 'vpscs-subscription-list'; list.className = 'vpscs-subscription-list';
          list.innerHTML = items.map(function (item, index) {
            var used = Number(item.u || 0) + Number(item.d || 0);
            var total = Number(item.transfer_enable || 0);
            var expires = item.expired_at ? new Date(Number(item.expired_at) * 1000).toLocaleDateString() : '长期有效';
            return '<section class="vpscs-subscription-card">'
              + '<div class="vpscs-subscription-head"><span class="vpscs-subscription-name">' + escapeHtml(item.name || '订阅') + '</span><span>到期：' + expires + '</span></div>'
              + '<p class="vpscs-subscription-meta">已用 ' + (used / 1073741824).toFixed(2) + ' GB / ' + (total / 1073741824).toFixed(2) + ' GB</p>'
              + '<div class="vpscs-subscription-url">' + escapeHtml(item.subscribe_url) + '</div>'
              + '<div class="vpscs-subscription-actions"><button type="button" data-renew="' + index + '">续费订阅</button><button type="button" data-copy="' + index + '">复制订阅</button></div>'
              + '</section>';
          }).join('');
          content.replaceChildren(list);
          list.addEventListener('click', function (event) {
            var renew = event.target.closest('[data-renew]');
            var copy = event.target.closest('[data-copy]');
            if (renew) {
              var item = subscriptionItems[Number(renew.getAttribute('data-renew'))] || {};
              var planId = item.plan && item.plan.id ? item.plan.id : '';
              sessionStorage.setItem(RENEW_KEY, JSON.stringify({ id: Number(item.id || 0), plan: planId ? Number(planId) : 0 }));
              location.hash = planId ? '#/plan/' + planId : '#/plan';
            }
            if (copy) {
              var target = subscriptionItems[Number(copy.getAttribute('data-copy'))] || {};
              openCopyDialog(target.subscribe_url || '', target.name || '我的订阅');
            }
          });
        }).catch(function (error) {
          var failed = document.createElement('div');
          failed.className = 'vpscs-subscription-loading is-error';
          failed.innerHTML = '<span>' + escapeHtml(error.message || '订阅加载失败') + '</span><button type="button">重新加载</button>';
          failed.querySelector('button').addEventListener('click', function () { content.dataset.vpscsSubscriptionManaged = ''; content.replaceChildren(); renderSubscriptionList(); });
          content.replaceChildren(failed);
        }).finally(function () { subscriptionRenderBusy = false; });
      }

      // 订阅操作弹窗：对齐原「一键订阅」弹窗的四项入口（扫码 / 复制 / 导入 Clash / 导入 Hiddify）+ 使用教程。
      var copyDialog = null;
      var copyTarget = { url: '', name: '' };
      var ICON_QR = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><path d="M14 14h3v3h-3zM20 14h1M14 20h1M18 18h3v3h-3z"/></svg>';
      var ICON_COPY = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="8" y="3" width="12" height="15" rx="2.5"/><path d="M16 21H6.5A2.5 2.5 0 0 1 4 18.5V8"/></svg>';
      var ICON_CLASH = '<span class="vpscs-client-icon is-clash"></span>';
      var ICON_HIDDIFY = '<span class="vpscs-client-icon is-hiddify"></span>';

      function closeCopyDialog() {
        if (!copyDialog) return;
        copyDialog.classList.remove('is-open');
        document.body.classList.remove('vpscs-modal-open');
      }
      function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
          return navigator.clipboard.writeText(text).then(function () { return true; }).catch(function () { return legacyCopy(text); });
        }
        return Promise.resolve(legacyCopy(text));
      }
      function legacyCopy(text) {
        var area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.top = '-1000px';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        var ok = false;
        try { ok = document.execCommand('copy'); } catch (_) {}
        document.body.removeChild(area);
        return ok;
      }
      function ensureQrLib(done) {
        if (window.qrcode) { done(null); return; }
        var script = document.createElement('script');
        script.src = '/theme/{{$theme}}/assets/qrcode.js?v={{$assetVer}}';
        script.onload = function () { done(window.qrcode ? null : new Error('二维码组件加载失败')); };
        script.onerror = function () { done(new Error('二维码组件加载失败')); };
        document.head.appendChild(script);
      }
      function renderCopyMenu() {
        var body = copyDialog.querySelector('.vpscs-copy-body');
        body.className = 'vpscs-copy-body';
        body.innerHTML = '<div class="vpscs-copy-list">'
          + '<button type="button" class="vpscs-copy-item" data-act="qr"><span class="vpscs-copy-ico">' + ICON_QR + '</span><span class="vpscs-copy-label">扫描二维码订阅</span></button>'
          + '<button type="button" class="vpscs-copy-item" data-act="copy"><span class="vpscs-copy-ico">' + ICON_COPY + '</span><span class="vpscs-copy-label">复制订阅地址</span></button>'
          + '<button type="button" class="vpscs-copy-item" data-act="clash"><span class="vpscs-copy-ico">' + ICON_CLASH + '</span><span class="vpscs-copy-label">导入到 Clash</span></button>'
          + '<button type="button" class="vpscs-copy-item" data-act="hiddify"><span class="vpscs-copy-ico">' + ICON_HIDDIFY + '</span><span class="vpscs-copy-label">导入到 Hiddify</span></button>'
          + '</div>';
      }
      function renderCopyQr() {
        var body = copyDialog.querySelector('.vpscs-copy-body');
        body.innerHTML = '<div class="vpscs-copy-qr"><div class="vpscs-copy-qr-box">正在生成二维码…</div>'
          + '<p class="vpscs-copy-qr-tip">使用支持扫码的客户端进行订阅</p>'
          + '<button type="button" class="vpscs-copy-back">返回</button></div>';
        var box = body.querySelector('.vpscs-copy-qr-box');
        ensureQrLib(function (error) {
          if (error) { box.textContent = error.message; return; }
          try {
            var qr = window.qrcode(0, 'M');
            qr.addData(copyTarget.url);
            qr.make();
            box.innerHTML = qr.createSvgTag({ cellSize: 6, margin: 2, scalable: true });
          } catch (_) { box.textContent = '二维码生成失败，请改用「复制订阅地址」'; }
        });
      }
      function openCopyDialog(url, title) {
        copyTarget.url = url || '';
        copyTarget.name = title || '';
        if (!copyDialog) {
          copyDialog = document.createElement('div');
          copyDialog.className = 'vpscs-copy-modal';
          copyDialog.innerHTML = '<div class="vpscs-copy-backdrop" data-close></div>'
            + '<div class="vpscs-copy-panel" role="dialog" aria-modal="true" aria-label="订阅操作">'
            + '<div class="vpscs-copy-head"><span class="vpscs-copy-title"></span><button type="button" class="vpscs-copy-close" data-close aria-label="关闭">×</button></div>'
            + '<div class="vpscs-copy-body"></div>'
            + '<button type="button" class="vpscs-copy-tutorial">不会使用，查看使用教程</button>'
            + '</div>';
          document.body.appendChild(copyDialog);
          copyDialog.addEventListener('click', function (event) {
            if (event.target.closest('[data-close]')) { closeCopyDialog(); return; }
            if (event.target.closest('.vpscs-copy-back')) { renderCopyMenu(); return; }
            var item = event.target.closest('.vpscs-copy-item');
            if (item) { runCopyAction(item); return; }
            if (event.target.closest('.vpscs-copy-tutorial')) {
              closeCopyDialog();
              landingRequested = false;
              document.body.classList.remove('vpscs-landing-mode');
              loadUserApp();
              location.hash = '#/knowledge';
            }
          });
          addEventListener('keydown', function (event) { if (event.key === 'Escape') closeCopyDialog(); });
        }
        copyDialog.querySelector('.vpscs-copy-title').textContent = copyTarget.name ? copyTarget.name + ' · 订阅操作' : '订阅操作';
        renderCopyMenu();
        copyDialog.classList.add('is-open');
        document.body.classList.add('vpscs-modal-open');
      }
      function runCopyAction(item) {
        var act = item.getAttribute('data-act');
        var label = item.querySelector('.vpscs-copy-label');
        if (act === 'qr') { renderCopyQr(); return; }
        if (act === 'copy') {
          copyText(copyTarget.url).then(function (ok) {
            if (ok) {
              label.textContent = '已复制订阅地址 ✓';
              item.classList.add('is-ok');
              setTimeout(function () { label.textContent = '复制订阅地址'; item.classList.remove('is-ok'); }, 2000);
            } else {
              label.textContent = '复制失败，请长按弹窗内地址';
              var body = copyDialog.querySelector('.vpscs-copy-body');
              if (!body.querySelector('.vpscs-copy-fallback')) {
                var fallback = document.createElement('textarea');
                fallback.className = 'vpscs-copy-fallback';
                fallback.readOnly = true;
                fallback.rows = 3;
                fallback.value = copyTarget.url;
                body.appendChild(fallback);
              }
              var field = body.querySelector('.vpscs-copy-fallback');
              field.focus();
              field.select();
            }
          });
          return;
        }
        var appName = (window.settings && window.settings.title) || '订阅';
        if (act === 'clash') {
          location.href = 'clash://install-config?url=' + encodeURIComponent(copyTarget.url) + '&name=' + encodeURIComponent(appName);
          return;
        }
        if (act === 'hiddify') {
          location.href = 'hiddify://import/' + copyTarget.url + '#' + encodeURIComponent(appName);
        }
      }

      // 「捷径」里的一键订阅 / 续费订阅 / 购买订阅都已并入订阅卡，整行（含可点击外壳）
      // 都隐藏，不留空白可点区域。隐藏发生在 MutationObserver 的微任务里，早于下一帧绘制，
      // 所以刷新时不会再先闪现一下再消失。
      function pruneShortcutRows() {
        var card = document.querySelector('#app .n-card.vpscs-shortcuts');
        if (!card) {
          var headers = document.querySelectorAll('#app .n-card__header, #app .n-card-header, #app .n-card__header-main, #app .n-card-header__main');
          for (var i = 0; i < headers.length; i += 1) {
            if (headers[i].textContent.replace(/\s+/g, '') === '捷径') { card = headers[i].closest('.n-card'); break; }
          }
          if (!card) return;
          card.classList.add('vpscs-shortcuts');
        }
        var content = card.querySelector('.n-card__content') || card;
        var labels = ['一键订阅', '续费订阅', '购买订阅', '购买新订阅'];
        var pending = 0;
        labels.forEach(function (label) {
          var nodes = Array.prototype.slice.call(content.querySelectorAll('div,li,section'));
          var hits = nodes.filter(function (node) {
            var text = node.textContent.replace(/\s+/g, '');
            // 标题必须开头就出现，且整行只有标题 + 一句说明，避免误伤整个入口列表。
            return text.indexOf(label) === 0 && text.length < 44 && (node.querySelector('div') || node.tagName !== 'DIV');
          });
          var row = hits.filter(function (node) { return hits.indexOf(node.parentElement) === -1; })[0];
          if (!row) return;
          // 一路向上找到这一行真正带点击事件的外壳（文本仍以该标题开头才继续上升，
          // 避免把包含其它入口的整个列表容器一起隐藏掉）。
          var shell = row;
          while (shell.parentElement && shell.parentElement !== content) {
            var up = shell.parentElement;
            var upText = up.textContent.replace(/\s+/g, '');
            if (upText.indexOf(label) !== 0 || upText.length > 60) break;
            shell = up;
          }
          if (shell.style.display !== 'none') pending += 1;
          shell.style.setProperty('display', 'none', 'important');
          shell.style.setProperty('pointer-events', 'none', 'important');
        });
        // 清掉可能残留的空壳（无文字、无高度的行）
        Array.prototype.forEach.call(content.children, function (child) {
          if (child.style.display === 'none') return;
          if (!child.textContent.replace(/\s+/g, '') && !child.querySelector('img,svg')) {
            child.style.setProperty('display', 'none', 'important');
            child.style.setProperty('pointer-events', 'none', 'important');
          }
        });
        return pending;
      }
      // Some browsers finish restoring the cached Vue view without producing
      // another child-list mutation.  A light poll makes the enhancement
      // deterministic after refresh and stops doing work once it is rendered.
      setInterval(renderSubscriptionList, 900);
      setInterval(pruneShortcutRows, 900);
      setInterval(syncPlanShelf, 900);
      setInterval(enhancePlanPeriod, 900);
    })();
  </script>
</body>

</html>
