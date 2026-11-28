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
  body.vpscs-shelf-mode #app .card-container { display: none !important; }
  .vpscs-plan-shelf { margin-top: 4px; }
  .vpscs-plan-shelf .vl-plan { cursor: pointer; transition: transform .2s, border-color .2s; }
  .vpscs-plan-shelf .vl-plan:hover { transform: translateY(-2px); }
  .vpscs-plan-shelf .vl-btn { width: 100%; border: 0; cursor: pointer; font-family: inherit; font-size: 16px; }
  .vl-plan-benefits .is-off { color: #6b7280; }
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
      // 续费时要把 subscription_id 带进下单请求，否则后端会把续费判成新购/升级。
      // 应用内部走的是 axios(XHR)，不经过 window.fetch，两条通道都要挂。
      var RENEW_URL = /\/api\/v1\/user\/order\/save(?:\?|$)/;
      function takeRenewId() {
        var target = sessionStorage.getItem('vpscs_renew_subscription_id');
        if (target) sessionStorage.removeItem('vpscs_renew_subscription_id');
        return target;
      }
      function withRenewId(body) {
        try {
          var payload = JSON.parse(body);
          payload.subscription_id = Number(takeRenewId());
          return JSON.stringify(payload);
        } catch (_) { return body; }
      }
      var nativeFetch = window.fetch.bind(window);
      window.fetch = function (input, init) {
        var url = typeof input === 'string' ? input : input.url;
        if (RENEW_URL.test(url) && sessionStorage.getItem('vpscs_renew_subscription_id') && init && typeof init.body === 'string') {
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
        if (RENEW_URL.test(this.__vpscsUrl || '') && sessionStorage.getItem('vpscs_renew_subscription_id') && typeof body === 'string') {
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
          return parsed.slice(0, 4).map(function (item) {
            var ok = item && item.support !== false;
            return '<li class="' + (ok ? '' : 'is-off') + '">' + (ok ? '✓ ' : '✗ ') + escapeHtml((item && item.feature) || '') + '</li>';
          }).join('');
        }
        var text = raw.replace(/<br\s*\/?>/gi, '\n').replace(/<\/(p|div|li|h[1-6])>/gi, '\n').replace(/<[^>]+>/g, '');
        return text.split(/\r?\n/).map(function (line) { return line.trim(); }).filter(Boolean).slice(0, 4)
          .map(function (line) { return '<li>✓ ' + escapeHtml(line) + '</li>'; }).join('');
      }

      function planCardHtml(plan, action) {
        var one = plan.onetime_price != null && Number(plan.onetime_price) > 0;
        var selected = one ? ['onetime_price', '一次性'] : periods.find(function (p) { return plan[p[0]] != null && Number(plan[p[0]]) > 0; });
        if (!selected) selected = ['onetime_price', '一次性'];
        var price = Number(plan[selected[0]] || 0) / 100;
        var suffix = one ? '' : ' / ' + selected[1];
        var extra = one ? '一次购买，长期有效' : periods.filter(function (p) { return plan[p[0]] != null && Number(plan[p[0]]) > 0 && p[0] !== selected[0]; }).slice(0, 3).map(function (p) { return p[1] + '付 ¥' + (Number(plan[p[0]]) / 100).toFixed(2); }).join(' · ');
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

      // Rename the legacy filter label without altering internal period keys.
      new MutationObserver(function () {
        document.querySelectorAll('label,button,span').forEach(function (node) {
          var label = node.childElementCount === 0 ? node.textContent.trim() : '';
          var action = node.closest('button,a');
          if (action && ['查看', '查看详情'].includes(label)) action.classList.add('vpscs-action-view');
          if (action && ['关闭', '取消'].includes(label)) action.classList.add('vpscs-action-danger');
          if (label === '按流量') node.textContent = '一次性套餐';
          if (label === '购买订阅') {
            node.textContent = '购买新订阅';
            node.closest('button,a')?.addEventListener('click', function () { sessionStorage.removeItem('vpscs_renew_subscription_id'); }, { once: true });
          }
        });
        enforcePlanGrid();
        renderSubscriptionList();
        pruneShortcutRows();
      }).observe(document.getElementById('app'), { childList: true, subtree: true });

      function enforcePlanGrid() {
        if (!/^#\/plan(?:\?|$)/i.test(location.hash)) return;
        var grid = document.querySelector('#app .card-container');
        if (!grid) return;
        var columns = innerWidth <= 680 ? 1 : (innerWidth <= 1000 ? 2 : 3);
        grid.style.setProperty('display', 'grid', 'important');
        grid.style.setProperty('grid-template-columns', 'repeat(' + columns + ', minmax(0, 1fr))', 'important');
        Array.from(grid.children).forEach(function (item) {
          item.style.setProperty('width', 'auto', 'important');
          item.style.setProperty('min-width', '0', 'important');
          var card = item.querySelector('.n-card');
          if (card) card.style.setProperty('width', '100%', 'important');
        });
      }
      addEventListener('resize', enforcePlanGrid, { passive: true });
      addEventListener('hashchange', function () { setTimeout(enforcePlanGrid, 0); });

      // 用户中心「购买订阅」：隐藏应用原生卡片，用与着陆页完全相同的模板重绘。
      var shelfPlans = null;
      var shelfLoading = false;

      function planShelfMode() { return /^#\/plan\/?$/.test(location.hash); }

      function shelfFilterMode() {
        var items = Array.prototype.slice.call(document.querySelectorAll('#app .n-base-segmented-item'));
        var index = -1;
        items.forEach(function (item, i) {
          if (item.classList.contains('n-base-segmented-item--active') || item.getAttribute('aria-checked') === 'true') index = i;
        });
        return index === 1 ? 'period' : (index === 2 ? 'onetime' : 'all');
      }

      function paintPlanShelf() {
        var shelf = document.getElementById('vpscs-plan-shelf');
        if (!shelf || !shelfPlans) return;
        var plans = filterPlans(shelfPlans, shelfFilterMode());
        if (!plans.length) { shelf.innerHTML = '<li class="vl-empty">该分类暂无在售套餐</li>'; return; }
        shelf.innerHTML = plans.map(function (plan) {
          return planCardHtml(plan, '<button type="button" class="vl-btn" data-buy="' + escapeHtml(String(plan.id || '')) + '">立即订阅</button>');
        }).join('');
      }

      function mountPlanShelf() {
        if (!planShelfMode() || document.getElementById('vpscs-plan-shelf')) return;
        var grid = document.querySelector('#app .card-container');
        if (!grid) return;
        var shelf = document.createElement('ul');
        shelf.id = 'vpscs-plan-shelf';
        shelf.className = 'vl-plans vpscs-plan-shelf';
        grid.parentNode.insertBefore(shelf, grid.nextSibling);
        document.body.classList.add('vpscs-shelf-mode');
        if (shelfPlans) { paintPlanShelf(); return; }
        if (shelfLoading) return;
        shelfLoading = true;
        shelf.innerHTML = '<li class="vl-empty">正在加载套餐…</li>';
        nativeFetch('/api/v1/user/plan/fetch', { headers: authHeaders(), credentials: 'same-origin' })
          .then(function (r) { if (!r.ok) throw new Error(r.status === 403 ? '登录已过期，请重新登录' : '套餐加载失败（' + r.status + '）'); return r.json(); })
          .then(function (payload) { shelfPlans = payload.data || []; paintPlanShelf(); })
          .catch(function (error) { shelf.innerHTML = '<li class="vl-empty">' + escapeHtml(error.message || '套餐加载失败') + '</li>'; })
          .finally(function () { shelfLoading = false; });
      }

      function syncPlanShelf() {
        if (!planShelfMode()) {
          var shelf = document.getElementById('vpscs-plan-shelf');
          if (shelf) shelf.remove();
          document.body.classList.remove('vpscs-shelf-mode');
          return;
        }
        mountPlanShelf();
      }

      document.addEventListener('click', function (event) {
        if (!planShelfMode()) return;
        var buy = event.target.closest('#vpscs-plan-shelf [data-buy]');
        var card = event.target.closest('#vpscs-plan-shelf .vl-plan');
        if (buy) { event.preventDefault(); location.hash = '#/plan/' + buy.getAttribute('data-buy'); return; }
        if (card) { location.hash = '#/plan/' + (card.getAttribute('data-plan-id') || ''); return; }
        if (event.target.closest('#app .n-base-segmented-item')) setTimeout(paintPlanShelf, 0);
      }, true);
      addEventListener('hashchange', function () { setTimeout(syncPlanShelf, 0); });

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
              sessionStorage.setItem('vpscs_renew_subscription_id', String(item.id || ''));
              var planId = item.plan && item.plan.id ? item.plan.id : '';
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

      // 复制订阅：和「捷径 - 一键订阅」的弹窗一样，先弹窗再复制，移动端也能长按选中。
      var copyDialog = null;
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
      function openCopyDialog(url, title) {
        if (!copyDialog) {
          copyDialog = document.createElement('div');
          copyDialog.className = 'vpscs-copy-modal';
          copyDialog.innerHTML = '<div class="vpscs-copy-backdrop" data-close></div>'
            + '<div class="vpscs-copy-panel" role="dialog" aria-modal="true" aria-label="复制订阅链接">'
            + '<div class="vpscs-copy-head"><strong>复制订阅链接</strong><button type="button" class="vpscs-copy-close" data-close aria-label="关闭">×</button></div>'
            + '<p class="vpscs-copy-sub"></p>'
            + '<textarea class="vpscs-copy-field" readonly rows="3" spellcheck="false"></textarea>'
            + '<p class="vpscs-copy-hint">复制后粘贴到 v2rayN / Clash Verge / Shadowrocket 等客户端的「从剪贴板导入」即可。</p>'
            + '<div class="vpscs-copy-actions"><button type="button" class="vpscs-copy-btn vpscs-copy-confirm">复制链接</button><button type="button" class="vpscs-copy-btn is-ghost" data-close>关闭</button></div>'
            + '</div>';
          document.body.appendChild(copyDialog);
          copyDialog.addEventListener('click', function (event) { if (event.target.closest('[data-close]')) closeCopyDialog(); });
          copyDialog.querySelector('.vpscs-copy-confirm').addEventListener('click', function () {
            var field = copyDialog.querySelector('.vpscs-copy-field');
            var button = copyDialog.querySelector('.vpscs-copy-confirm');
            copyText(field.value).then(function (ok) {
              button.textContent = ok ? '已复制 ✓' : '请长按选中复制';
              button.classList.toggle('is-ok', !!ok);
              field.focus();
              field.select();
              setTimeout(function () { button.textContent = '复制链接'; button.classList.remove('is-ok'); }, 2000);
            });
          });
          addEventListener('keydown', function (event) { if (event.key === 'Escape') closeCopyDialog(); });
        }
        copyDialog.querySelector('.vpscs-copy-sub').textContent = title;
        copyDialog.querySelector('.vpscs-copy-field').value = url || '';
        copyDialog.classList.add('is-open');
        document.body.classList.add('vpscs-modal-open');
        setTimeout(function () {
          var field = copyDialog.querySelector('.vpscs-copy-field');
          field.focus();
          field.select();
        }, 40);
      }

      // 「捷径」里的一键订阅/续费订阅已经并入订阅卡，隐藏这两行避免入口重复。
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
        ['一键订阅', '续费订阅'].forEach(function (label) {
          var nodes = Array.prototype.slice.call(card.querySelectorAll('div'));
          var hits = nodes.filter(function (node) {
            var text = node.textContent.replace(/\s+/g, '');
            return text.indexOf(label) === 0 && text.length < 40 && node.querySelector('div');
          });
          var row = hits.filter(function (node) { return hits.indexOf(node.parentElement) === -1; })[0];
          if (row) row.style.setProperty('display', 'none', 'important');
        });
      }
      // Some browsers finish restoring the cached Vue view without producing
      // another child-list mutation.  A light poll makes the enhancement
      // deterministic after refresh and stops doing work once it is rendered.
      setInterval(renderSubscriptionList, 900);
      setInterval(pruneShortcutRows, 900);
      setInterval(syncPlanShelf, 900);
    })();
  </script>
</body>

</html>
