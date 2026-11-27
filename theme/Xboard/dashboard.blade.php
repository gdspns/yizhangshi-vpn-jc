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
  #vpscs-landing { display: none; min-height: 100vh; color: #15151a; background: #fff; font-family: Inter, system-ui, sans-serif; }
  body.vpscs-landing-mode #vpscs-landing { display: block; }
  body.vpscs-landing-mode #app { display: none; }
  .vl-wrap { width: min(1120px, calc(100% - 40px)); margin: auto; }
  .vl-top { position: sticky; top: 0; z-index: 20; border-bottom: 1px solid #ececf2; background: rgba(255,255,255,.9); backdrop-filter: blur(14px); }
  .vl-top-in { height: 68px; display: flex; align-items: center; justify-content: space-between; }
  .vl-brand { display: flex; align-items: center; gap: 10px; color: #17171c; font-size: 20px; font-weight: 800; text-decoration: none; }
  .vl-mark { width: 28px; height: 28px; border: 7px solid #202028; border-right-color: #7c3aed; border-radius: 50%; transform: rotate(-20deg); }
  .vl-actions, .vl-cta { display: flex; gap: 12px; align-items: center; }
  .vl-link { color: #33333b; text-decoration: none; font-weight: 600; }
  .vl-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 22px; border-radius: 10px; color: #fff; background: #17171c; text-decoration: none; font-weight: 700; transition: transform .2s, background .2s; }
  .vl-btn:hover { transform: translateY(-1px); background: #7c3aed; }
  .vl-btn-soft { color: #27272f; background: #f0eff8; }
  .vl-hero { padding: 92px 0 78px; background: radial-gradient(circle at 75% 20%, #eee8ff 0, transparent 35%), linear-gradient(180deg,#fff,#faf9ff); }
  .vl-hero-in { display: grid; grid-template-columns: 1.1fr .9fr; gap: 72px; align-items: center; }
  .vl-eyebrow { display: inline-flex; gap: 8px; align-items: center; padding: 8px 12px; border-radius: 999px; color: #6d28d9; background: #f1eafe; font-weight: 700; }
  .vl-dot { width: 8px; height: 8px; border-radius: 50%; background: #7c3aed; }
  .vl-title { margin: 22px 0 18px; font-size: clamp(42px, 7vw, 76px); line-height: 1.02; letter-spacing: -.05em; }
  .vl-lead { max-width: 620px; color: #646470; font-size: 18px; line-height: 1.8; }
  .vl-cta { margin-top: 30px; }
  .vl-platforms { margin-top: 18px; color: #858590; }
  .vl-phone { width: min(330px, 100%); margin: auto; padding: 18px; border: 10px solid #18181d; border-radius: 38px; background: #f7f6fb; box-shadow: 0 30px 80px rgba(45,31,90,.22); }
  .vl-phone-top { display: flex; gap: 8px; align-items: center; font-weight: 800; }
  .vl-mini-card { margin-top: 34px; padding: 22px; border-radius: 20px; color: #fff; background: linear-gradient(145deg,#17171d,#443080); }
  .vl-mini-label { color: #c9c0e8; font-size: 13px; }
  .vl-mini-plan { min-height: 48px; font-weight: 750; }
  .vl-bar { height: 8px; border-radius: 20px; background: rgba(255,255,255,.2); overflow: hidden; }
  .vl-bar span { display: block; width: 36%; height: 100%; background: #a78bfa; }
  .vl-mini-button { margin-top: 22px; padding: 11px; border-radius: 10px; text-align: center; background: #7c3aed; }
  .vl-section { padding: 82px 0; }
  .vl-soft { background: #f7f7fa; }
  .vl-center { text-align: center; }
  .vl-h2 { margin: 0 0 14px; font-size: clamp(30px,4vw,44px); letter-spacing: -.035em; }
  .vl-sub { margin: 0 auto 38px; max-width: 660px; color: #73737f; font-size: 17px; line-height: 1.7; }
  .vl-features, .vl-plans { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; padding: 0; list-style: none; }
  .vl-feature, .vl-plan { padding: 26px; border: 1px solid #e6e5ec; border-radius: 18px; background: #fff; }
  .vl-feature-icon { width: 42px; height: 42px; display: grid; place-items: center; border-radius: 12px; color: #6d28d9; background: #f0eafe; font-size: 22px; }
  .vl-feature h3, .vl-plan h3 { margin: 18px 0 10px; font-size: 20px; }
  .vl-feature p, .vl-plan-desc { color: #73737f; line-height: 1.7; white-space: pre-line; }
  .vl-kind { display: inline-flex; padding: 5px 9px; border-radius: 999px; color: #6d28d9; background: #f0eafe; font-size: 13px; font-weight: 700; }
  .vl-price { margin: 22px 0 5px; font-size: 30px; font-weight: 850; }
  .vl-price-note { min-height: 44px; color: #777784; font-size: 14px; line-height: 1.5; }
  .vl-plan .vl-btn { width: 100%; box-sizing: border-box; }
  .vl-empty { grid-column: 1/-1; padding: 30px; text-align: center; color: #777; }
  .vl-final { padding: 58px; border-radius: 24px; color: #fff; text-align: center; background: linear-gradient(135deg,#17171d,#4c2a83); }
  .vl-final .vl-btn { color: #17171d; background: #fff; }
  .vl-foot { padding: 30px 0; border-top: 1px solid #ececf2; color: #777; }
  .vl-foot-in { display: flex; justify-content: space-between; }
  @media (max-width: 820px) {
    .vl-hero-in { grid-template-columns: 1fr; text-align: center; }
    .vl-lead { margin-inline: auto; }
    .vl-cta { justify-content: center; }
    .vl-features, .vl-plans { grid-template-columns: 1fr; }
    .vl-phone { display: none; }
    .vl-hero { padding: 64px 0; }
    .vl-section { padding: 58px 0; }
    .vl-final { padding: 38px 22px; }
  }
</style>

<body>

  <div id="vpscs-landing">
    <header class="vl-top"><div class="vl-wrap vl-top-in">
      <a class="vl-brand" href="/"><span class="vl-mark"></span><span>{{ $title }}</span></a>
      <nav class="vl-actions"><a class="vl-link vl-enter-app" href="#/login">登录</a><a class="vl-btn vl-enter-app" href="#/register">注册</a></nav>
    </div></header>
    <main>
      <section class="vl-hero"><div class="vl-wrap vl-hero-in">
        <div><p class="vl-eyebrow"><span class="vl-dot"></span>稳定、安全、简单易用</p><h1 class="vl-title">{{ $title }}</h1><p class="vl-lead">让复杂的线路配置留在后台，为您提供清晰的套餐选择与便捷的全平台连接体验。</p><p class="vl-cta"><a class="vl-btn vl-enter-app" href="#/register">立即注册</a><a class="vl-btn vl-btn-soft vl-enter-app" href="#/login">登录</a></p><p class="vl-platforms">支持 iPhone、安卓、Mac 与 Windows</p></div>
        <div class="vl-phone"><p class="vl-phone-top"><span class="vl-mark"></span>{{ $title }}</p><div class="vl-mini-card"><p class="vl-mini-label">我的套餐</p><p class="vl-mini-plan">高速稳定 · 多平台通用</p><p class="vl-bar"><span></span></p><p>本期流量使用情况</p><p class="vl-mini-button">复制订阅链接</p></div></div>
      </div></section>
      <section class="vl-section"><div class="vl-wrap"><h2 class="vl-h2 vl-center">为速度与稳定而做</h2><p class="vl-sub vl-center">简单选择套餐，登录后即可获取订阅并开始使用。</p><ul class="vl-features">
        <li class="vl-feature"><span class="vl-feature-icon">⚡</span><h3>高速稳定</h3><p>多线路智能调度，满足日常浏览、视频与工作需求。</p></li>
        <li class="vl-feature"><span class="vl-feature-icon">⌁</span><h3>一键添加</h3><p>复制订阅链接，在客户端添加一次，后续打开即可连接。</p></li>
        <li class="vl-feature"><span class="vl-feature-icon">▣</span><h3>全平台通用</h3><p>支持手机和电脑，一个账号管理套餐、流量与设备。</p></li>
      </ul></div></section>
      <section class="vl-section vl-soft" id="plans"><div class="vl-wrap"><h2 class="vl-h2 vl-center">套餐与价格</h2><p class="vl-sub vl-center">以下内容实时读取后台已上架商品，修改套餐后首页自动同步。</p><ul id="vl-plan-list" class="vl-plans"><li class="vl-empty">正在加载套餐…</li></ul></div></section>
      <section class="vl-section"><div class="vl-wrap"><div class="vl-final"><h2 class="vl-h2">现在开始</h2><p>注册账号、选择套餐，几分钟即可完成配置。</p><p class="vl-cta" style="justify-content:center"><a class="vl-btn vl-enter-app" href="#/register">立即注册</a><a class="vl-link vl-enter-app" style="color:#fff" href="#/login">已有账号，去登录</a></p></div></div></section>
    </main>
    <footer class="vl-foot"><div class="vl-wrap vl-foot-in"><span>© {{ $title }}</span><span><a class="vl-link vl-enter-app" href="#/login">登录</a> · <a class="vl-link vl-enter-app" href="#/register">注册</a></span></div></footer>
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
      function inLanding() { return !location.hash || location.hash === '#' || location.hash === '#/'; }
      function syncView() { document.body.classList.toggle('vpscs-landing-mode', inLanding()); }
      syncView();
      addEventListener('hashchange', syncView);
      document.querySelectorAll('.vl-enter-app').forEach(function (link) { link.addEventListener('click', function () { document.body.classList.remove('vpscs-landing-mode'); }); });

      var periods = [
        ['month_price', '月'], ['quarter_price', '季'], ['half_year_price', '半年'],
        ['year_price', '年'], ['two_year_price', '两年'], ['three_year_price', '三年']
      ];
      fetch('/api/v1/guest/plan/fetch').then(function (r) { return r.json(); }).then(function (payload) {
        var plans = payload.data || [];
        var list = document.getElementById('vl-plan-list');
        if (!plans.length) { list.innerHTML = '<li class="vl-empty">暂无在售套餐</li>'; return; }
        list.innerHTML = plans.map(function (plan) {
          var one = plan.onetime_price != null && Number(plan.onetime_price) > 0;
          var selected = one ? ['onetime_price', '一次性'] : periods.find(function (p) { return plan[p[0]] != null && Number(plan[p[0]]) > 0; });
          var price = selected ? Number(plan[selected[0]]) / 100 : 0;
          var kind = one ? '一次性套餐' : '周期套餐';
          var suffix = one ? '' : ' / ' + selected[1];
          var extra = one ? '一次购买，长期有效' : periods.filter(function (p) { return plan[p[0]] != null && Number(plan[p[0]]) > 0 && p[0] !== selected[0]; }).slice(0, 3).map(function (p) { return p[1] + '付 ¥' + (Number(plan[p[0]]) / 100).toFixed(2); }).join(' · ');
          var desc = String(plan.content || '').replace(/<[^>]+>/g, '').split('\n').slice(0, 3).join('\n');
          return '<li class="vl-plan"><span class="vl-kind">' + kind + '</span><h3>' + escapeHtml(plan.name) + '</h3><p class="vl-plan-desc">' + escapeHtml(desc) + '</p><p class="vl-price">¥' + price.toFixed(2) + suffix + '</p><p class="vl-price-note">' + escapeHtml(extra) + '</p><a class="vl-btn vl-enter-app" href="#/register">购买</a></li>';
        }).join('');
      }).catch(function () { document.getElementById('vl-plan-list').innerHTML = '<li class="vl-empty">套餐加载失败，请稍后刷新</li>'; });

      function escapeHtml(value) { var div = document.createElement('div'); div.textContent = value; return div.innerHTML; }

      // Rename the legacy filter label without altering internal period keys.
      new MutationObserver(function () {
        document.querySelectorAll('label,button,span').forEach(function (node) {
          if (node.childElementCount === 0 && node.textContent.trim() === '按流量') node.textContent = '一次性套餐';
        });
      }).observe(document.getElementById('app'), { childList: true, subtree: true });
    })();
  </script>
</body>

</html>
