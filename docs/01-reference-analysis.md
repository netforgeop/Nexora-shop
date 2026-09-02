# تحلیل مهندسی‌معکوس قالب مرجع «Creek» و معماری پیشنهادی Nexora Shop

> خروجی «مرحله ۱۵» بریف (`my-pr`): تحلیل کامل قالب مرجع (`creek.zip`) **قبل از نوشتن حتی یک خط کد جدید**.
> این سند مبنای تصمیم‌های طراحی و مهندسی پروژه‌ی فروشگاهی است.

---

## ۰. خلاصه‌ی اجرایی (TL;DR)

| موضوع | نتیجه‌ی بررسی |
|---|---|
| ماهیت قالب مرجع | قالب **شرکتی/ساخت‌وساز** (Construction) – راست‌چین‌شده‌ی یک قالب TemplateMonster/Novi Builder (نسخه‌ی «Trunk 2.0.0»)، زبان `fa`، `dir="rtl"` |
| حجم | ۲۴٫۵ MB زیپ – ۵۰ صفحه‌ی HTML، ۴۶۴KB `style.css`، ۸۰۶KB `core.min.js`، ۱۴MB فونت (۲۵ خانواده‌ی فارسی)، ۸٫۶MB تصویر، ۴٫۲MB صدا |
| CSS Framework | **Bootstrap v4.0.0-beta2** (سفارشی‌شده: کانتینر ۱۲۰۰/۱۴۰۰ و بریک‌پوینت `xxl` اضافه شده) |
| JS Stack | **jQuery 3.2.1** + باندل ۳۷ ماژولی (Swiper 3.4.2، Owl 2.2.1، Isotope 2.2.2، PhotoSwipe 4.1.1، RD Navbar 2.2.5، Regula، WOW، Select2، Slick، …) |
| Icon System | **Linearicons** (سیستم اصلی – ۱۰۹۷ گلیف، `linear-icon-*`) + Font Awesome 4.7 (فقط شبکه‌های اجتماعی) + MDI (یک بار!) |
| Font System | مکانیزم alias دو فونتی `primary-font` / `secondary-font` – پیش‌فرض **IRANYekan** (متن) + **Pinar** (تیترها)، `font-display: swap` |
| رنگ برند | زرد `#f6d014` روی خانواده‌ی مشکی/خاکستری (`#111 / #151515 / #333 / #888 / #f8f8f8`) |
| زبان طراحی | صنعتی، گوشه‌های تیز (radius 0)، خط زرد ۱px زیر تیترها، آیکون‌های خطی نازک، ریتم بخش‌های سفید/خاکستری/زرد/تیره |
| تصمیم کلان | حفظ **Design DNA** (رنگ، تایپوگرافی، ریتم، آیکون‌های Linearicons، گرید Bootstrap) و **بازنویسی کامل مهندسی** (بدون jQuery، CSS Variables، BEM، Semantic HTML، A11y) |

---

## ۱. Project Architecture Analysis

### ۱.۱ ساختار فایل‌ها

```
creek/
├── *.html (50 صفحه)              ← همه در ریشه، هدر/فوتر در هر فایل کپی شده (هر فایل ۳۰–۷۸KB)
├── css/
│   ├── bootstrap.css   155KB     ← BS 4.0.0-beta2 + xxl(1400px) + container 1200/1400
│   ├── style.css       464KB     ← "Trunk 2.0.0": reset → typography → colors → layout → components → utilities → plugins
│   ├── fonts.css       183KB     ← @font-face آیکون‌ها (FA 4.7، MDI 1.4.57، Linearicons) + @import فونت فارسی
│   └── farsi-fonts-styles/       ← 50 فایل: primary-*.css / secondary-*.css برای 25 فونت فارسی
├── js/
│   ├── core.min.js     806KB     ← باندل 37 کتابخانه (jQuery تا WOW)
│   ├── script.js        51KB     ← لایه‌ی init با الگوی `plugins = {...}` و data-attribute
│   └── html5shiv.min.js          ← IE<9
├── fonts/                        ← Linearicons.ttf (498KB!)، FA، MDI، fonts-files/ (156 فایل woff/woff2)
├── images/  (86 فایل)            ← عکس‌های ساخت‌وساز، تیم، لوگو مشتری، اسلایدر 1920x1080
├── images/layout-panel/          ← اسکرین‌شات دموها (پنل انتخاب دمو)
├── audio/sound.mp3   4.3MB       ← دموی «مطلب صوتی»
└── php/mail.php                  ← بک‌اند فرم تماس (RD Mailform)
```

### ۱.۲ آناتومی یک صفحه (index.html)

```html
<html class="wide wow-animation" lang="fa" dir="rtl">
<body>
  #page-loader (.cssload-speeding-wheel)          ← پری‌لودر تمام‌صفحه
  .page                                           ← ریشه‌ی لایه‌بندی (overflow hidden, min-height 100vh)
    .layout-panel-wrap                            ← پنل دمو (Isotope) – مختص دمو، حذف می‌شود
    header.page-header  (bg #151515, z 1000)
      .rd-navbar-wrap > nav.rd-navbar.rd-navbar-default [data-*-layout / stick-up]
        .rd-navbar-top-panel.rd-navbar-top-panel-dark   ← نوار بالایی: آدرس/تلفن (unit + linear-icon) + شبکه‌های اجتماعی (fa)
        .rd-navbar-inner.rd-navbar-search-wrap
          .rd-navbar-panel  → button.rd-navbar-toggle (همبرگر) + .rd-navbar-brand (لوگو 151x44 + srcset 2x)
          .rd-navbar-nav-wrap
             .rd-navbar-search (form.rd-search + floating label)  ← جستجوی جمع‌شونده
             ul.rd-navbar-nav > li[.active] > a + ul.rd-navbar-dropdown | ul.rd-navbar-megamenu (ستون‌ها با p.rd-megamenu-header)
    section (Swiper) .swiper-slider_fullheight               ← Hero: 3 اسلاید، کپشن با data-caption-animate="fadeInUpSmall" و delay پلکانی 0/200/350
    section.section-xs.section-cta.bg-image-7               ← نوار CTA زرد (h4 + دکمه)
    section.section-md.bg-default.text-center               ← «خدمات ما»: h4.heading-decorated + row.row-50 + 9× article.blurb-minimal
    section.bg-gray-lighter.object-wrap                     ← «تاریخچه»: متن + progress-linear + نیمه‌ی تصویری absolute
    section.section-md                                      ← «پروژه‌های در حال اجرا»: 6× article.post-project (تصویر + overlay + ذره‌بین)
    section.section-md.bg-accent                            ← شمارنده‌ها: 4× article.box-counter روی زرد
    section.section-lg                                      ← «پروژه‌های ما»: 8× .thumb-creative (کارت فلیپ سه‌بعدی)
    section.section-md.bg-accent.bg-image                   ← CTA دوم
    section.section-md                                      ← «مدیران»: 3× article.thumb-flat
    section.bg-gray-lighter.object-wrap                     ← فرم تماس (rd-mailform + data-constraints)
    section.section-md                                      ← اخبار: Owl Carousel (1/2/3 آیتم) + article.post-classic.post-minimal
    section.section-lg.bg-image.context-dark                ← نظرات: Owl + .quote-default (آواتار 120px دایره)
    section.section-md                                      ← لوگوی مشتریان: 8× figure.box-icon-image
    section.pre-footer-corporate.bg-gray-darker             ← فوتر اصلی 4 ستونه (برند، فهرست، تماس dl، نقشه)
    footer.footer-corporate.bg-gray-darkest                 ← کپی‌رایت + شبکه‌های اجتماعی
  .snackbars#form-output-global                             ← خروجی فرم (toast)
  .pswp                                                     ← مارک‌آپ PhotoSwipe
  script core.min.js + script.js
```

**الگوی تکرارشونده‌ی هر بخش:**

```
section.section-{xs|sm|md|lg|xl}.bg-{default|gray-lighter|accent|gray-dark|image}[.text-center][.context-dark]
  └ .container
      ├ h4.heading-decorated            ← تیتر بخش (خط زرد 35→50px زیرش)
      └ .row.row-{30|50|60}             ← فاصله‌ی عمودی آیتم‌ها
          └ .col-sm-6.col-lg-4.col-xl-3 ← ستون‌ها
              └ article.component       ← کامپوننت مستقل
```

### ۱.۳ دسته‌بندی ۵۰ صفحه

| گروه | صفحات | ارزش برای فروشگاه |
|---|---|---|
| خانه | `index`, `index-variant-2`, `index-variant-3` | ریتم بخش‌ها، Hero، CTA، کاروسل‌ها |
| هدرها (۹) | default, default-dark (`rd-navbar_half-dark`), modern, creative, corporate (دارای آیکون سبد `rd-navbar-nav-wrap__shop`!), minimal, transparent (`_boxed`), sidebar ×2 | الگوی Navbar سه‌لایه (top bar + brand/actions + nav) |
| فوترها (۳) | corporate, minimal, modern | فوتر ۴ ستونه + نوار کپی‌رایت |
| بلاگ (۶ لی‌اوت + ۷ فرمت پست) | classic/grid/masonry/justify، left/right/no-sidebar، standard/gallery/image/link/quote/video/audio | Blog grid، Sidebar widgets، Single post، Pagination |
| پروژه/خدمات/تیم | projects, project-category, single-project, services, single-service, our-team, team-member-profile | کارت‌های تصویری، گالری PhotoSwipe |
| عمومی | about, contacts, 404, coming-soon, search-results, privacy-policy | Search results، 404، فرم‌ها |
| عناصر (۸) | accordion, tabs, buttons, typography, countdown, animated/number/circles-counter | Tabs، Accordion، Countdown (Flash Sale)، Buttons |

### ۱.۴ فهرست کامپوننت‌ها (Component Inventory)

| کامپوننت | ساختار HTML | کلاس‌های کلیدی | CSS | JS | Responsive | الگوی UX |
|---|---|---|---|---|---|---|
| **Navbar** | `nav.rd-navbar` سه لایه | `rd-navbar-static` (≥992) / `rd-navbar-fixed` (<992)، `--is-stuck` | استاتیک: inner `min-height:100px; padding:21px 0`، چسبیده: `74px` و top-panel مخفی؛ لینک `#000` → hover/active `#f6d014`؛ دراپ‌داون `216px; padding:30px; bg:#fbfbfb`؛ مگامنو ستونی | RD Navbar 2.2.5 (data-attribute per breakpoint، stick-up، clone) | موبایل: پنل ثابت `56px` سفید + منوی off-canvas `280px` از راست (`translateX(110%)`) | Sticky nav، مگامنو، جستجوی جمع‌شونده |
| **Hero Slider** | `.swiper-container.swiper-slider_fullheight` | `data-loop`, `data-autoplay="5000"`, `data-slide-bg` | `min-height: calc(80vh - 56px)`، bg `#333`، pagination پایین 15px | Swiper 3.4.2 + انیمیشن کپشن پلکانی | کپشن با `col-lg-10 col-xxl-6` | Full-bleed hero، CTA واحد |
| **CTA strip** | `section.section-xs.section-cta` | `bg-image-7`, `.button-primary` سفید | دکمه‌ی سفید روی زرد → hover مشکی | – | `text-center text-md-left` | نوار تبدیل بین بخش‌ها |
| **Blurb** | `article.blurb.blurb-minimal > .unit` | `blurb-minimal__icon`, `blurb__title.heading-6` | آیکون `30px` زرد، media-object (`unit`) | – | `col-md-6 col-xl-4` | ویژگی/خدمات با آیکون |
| **Object-wrap** | `section.object-wrap > .section-lg + .object-wrap__body` | `__body-md-right/left`, `__body-sizing-1` | نیمه‌ی تصویری `position:absolute` در دسکتاپ | – | <992: تصویر زیر متن | Split section |
| **Post project** | `article.post-project > a.img-thumbnail-variant-1 + __body` | `.caption` | overlay `rgba(21,21,21,.5)` + آیکون ذره‌بین در hover | PhotoSwipe (`data-photo-swipe-item`) | `col-md-6 col-xl-4` | کارت تصویری با hover reveal |
| **Box counter** | `article.box-counter` | `__icon`, `.counter`, `__title` | روی `bg-accent` | jQuery CountTo (lazy در viewport) | `col-md-6 col-lg-3` | اعتمادسازی عددی |
| **Thumb creative** | `.thumb-creative > __inner > __front/__back` | `.desktop` gating | فلیپ سه‌بعدی `rotateY` با `0.7s cubic-bezier(.4,.2,.2,1)`، `preserve-3d` | – | فقط دسکتاپ فلیپ می‌شود | Hover interaction «پرمیوم» |
| **Thumb flat** | `article.thumb-flat` | `__image`, `__body`, `__subtitle` | ساده، بدون سایه | – | `col-md-6 col-lg-4` | کارت تیم |
| **Quote** | `.quote-default` | `__image` (120px دایره)، `.q`، `__cite` | روی تصویر تیره (`context-dark`) | Owl (dots + nav) | 1 آیتم | Testimonial |
| **Post classic** | `article.post-classic.post-minimal` | `post-classic-title`, `post-meta` | تصویر 418x315 (4:3) | Owl `data-items 1/2/3` | – | کارت بلاگ |
| **Form** | `.form-wrap > input.form-input + label.form-label` | `rd-mailform`, `data-constraints="@Required @Email"` | input `45px; bg #f8f8f8; radius 0` → focus `bg #fff; border #ececee`؛ Floating label | RDInputLabel + Regula + jQuery Form → `php/mail.php` → `.snackbars` | – | Floating label، پیام toast |
| **Buttons** | `a.button.button-primary` | `-default/-black/-gray-dark/-link`, `-xs/-sm/-lg/-xl`, `-circle/-round-2`, `-icon` | `radius 0; padding 12px 25px; 14px/17px; border 1px`؛ primary زرد → hover مشکی | – | – | دکمه‌ی مربعی صنعتی |
| **Pagination** | `ul.pagination-classic` | `li.active`, `a.icon` | خانه‌های `50×50; radius 4; bg #f8f8f8` → active شفاف با border `#ececee` | – | ≥1200: 18px | – |
| **Lists** | `ul.list-xxs/xs/sm/md`, `list-inline-*`, `list-tags`, `list-archive`, `dl.list-terms-minimal` | – | فاصله‌ی عمودی 11/16/15/30px | – | – | Sidebar widgets |
| **Tabs / Accordion** | `.tabs-custom.tabs-horizontal > ul.nav-custom-tabs` / `.card.card-custom > .card-custom-collapse.collapse` | – | استایل سفارشی روی Bootstrap JS | Bootstrap 4 Tab/Collapse | – | Product info tabs |
| **Countdown** | `.countdown.countdown-default[data-time]` | `data-format="dhms"` | – | jQuery Countdown | – | Flash sale |
| **Checkbox/Radio** | `input.checkbox-custom + .checkbox-custom-dummy` | – | پرشدن زرد وقتی `:checked` | script.js | – | فیلترها |
| **Snackbar** | `.snackbars` | `.icon-xxs` | `max-width 280px; bg #151515; padding 9px 16px; shadow 0 1px 4px rgba(0,0,0,.15)` | RD Mailform | – | Toast |
| **Page loader** | `#page-loader > .cssload-container` | – | اسپینر CSS | fade در `load` | – | – |
| **Footer** | `section.pre-footer-corporate` + `footer.footer-corporate` | `bg-gray-darker` (#151515) / `bg-gray-darkest` (#111) | `padding 50px 0` / `20px 0`، لینک‌ها `#888` → hover زرد | RD Google Map | `col-sm-10 col-md-6 col-lg-* col-xl-3` | فوتر ۴ ستونه |

### ۱.۵ معماری CSS

* **سه فایل جهانی** روی همه‌ی صفحات (بدون code-splitting): `bootstrap.css` → `style.css` → `fonts.css`.
* `style.css` ترتیب لایه‌ای دارد: **reset → typography → colors → main layout → components (blurb, thumb, post, quote, box…) → utilities (bg-*, text-*, list-*, unit, row-*) → plugin overrides (swiper, owl, rd-navbar, pswp, select2…)**.
* نام‌گذاری **BEM ناقص/ترکیبی**: `block__element` (`blurb-minimal__icon`) + `block_modifier` (`thumb-creative_no-cover`) + `block-modifier` (`button-primary`) + state classes (`.active`, `.opened`, `--is-stuck`).
* **بدون CSS Custom Properties** (تنها `:root` بوت‌استرپ)؛ رنگ زرد **۱۶۹ بار** به‌صورت hard-code تکرار شده.
* پیشوندهای vendor سنگین (`-webkit/-moz/-ms/-o`) و `!important`های `.page .button-*` برای غلبه بر specificity.
* RTL با **معکوس کردن دستی property های فیزیکی** (`margin-right`, `right: 19px`) انجام شده – نه logical properties.
* Mobile-first با `min-width` (۱۰۷× `768px`، ۹۶× `1200px`، ۴۶× `992px`، ۲۵× `1400px`، ۱۴× `576px`) + چند `max-width` استثنایی.
* `a:focus, button:focus { outline: none !important }` و `*:focus { outline: none }` ← **حذف کامل focus ring** (ضعف A11y).

### ۱.۶ معماری JavaScript

* `core.min.js` (۸۰۶KB) = **۳۷ ماژول** با هدرهای `@module` (فهرست کامل در بخش ۳).
* `script.js` یک **رجیستری `plugins`** می‌سازد (۳۵ سلکتور) و هر پلاگین را فقط اگر عنصرش در صفحه باشد init می‌کند؛ پیکربندی از **data-attribute** خوانده می‌شود (`data-items`, `data-loop`, `data-autoplay`, `data-lg-layout`, …). این الگوی «init از روی مارک‌آپ» ارزشمند است و در پروژه‌ی جدید حفظ می‌شود (با vanilla JS).
* فلگ `isNoviBuilder` (ویرایشگر Novi) در همه‌جا پخش است – کد مرده برای ما.
* Lazy-init برای counter/progress با `isScrolledIntoView` (scroll listener) – در پروژه‌ی جدید با `IntersectionObserver`.
* فرم‌ها: Regula (validation از `data-constraints`) + jQuery Form (ajax) + `php/mail.php` + snackbar.

---

## ۲. Technology Stack (گزارش مرحله ۲)

| عنوان | قالب مرجع |
|---|---|
| **Template Stack** | Static HTML5 (۵۰ صفحه) + CSS + jQuery؛ تولیدشده با Novi Builder («Trunk 2.0.0»)؛ راست‌چین‌شده برای rtl-theme؛ بک‌اند فقط `php/mail.php` |
| **CSS Framework** | Bootstrap **4.0.0-beta2** (Grid/Flex/Utilities/Tab/Collapse/Tooltip) – سفارشی: `container` = 540/720/960/**1200/1400**، بریک‌پوینت **xxl = 1400px** اضافه شده |
| **JS Libraries** | jQuery 3.2.1، jQuery Migrate 3.0، Popper 1.11، Bootstrap JS 4.0.0-beta2، Swiper 3.4.2، Owl Carousel 2.2.1، Slick 1.6.0، Isotope 2.2.2 (+Masonry/Outlayer/imagesLoaded)، PhotoSwipe 4.1.1 + Default UI، Magnific Popup 1.0.0، RD Navbar 2.2.5، RD Google Map 0.1.5، RD Audio 1.0.3، RD Calendar + Moment 2.12، Regula 1.3.4، jQuery Form 3.51، RDInputLabel، Select2 4.0.2، TouchSwipe 1.6.18، jQuery Easing 1.4، Device.js، Cookie، resize-event، mousewheel 3.1.13، Material Parallax 0.1.6، CountTo، circle-progress 1.2.2، TimeCircles 1.5.3، Countdown، Stepper 3.0.8، Bootstrap Material Datetimepicker 2.0، UIToTop، WOW 1.1.3، html5shiv |
| **Icon System** | **Linearicons** (icon-font، ۱۰۹۷ گلیف، `linear-icon-*`) – سیستم اصلی UI؛ **Font Awesome 4.7** (۷۸۶ گلیف – فقط برای facebook/twitter/google-plus/telegram/instagram/pinterest + کارت فلش `\f107` + play/volume)؛ **Material Design Icons 1.4.57** (۱۴۵۷ گلیف – فقط `mdi-dots-horizontal` ×8) |
| **Font System** | ۲۵ خانواده‌ی فارسی محلی (woff2+woff، ۳۰۰/۴۰۰/۵۰۰/۷۰۰) با **alias** `"primary-font"` و `"secondary-font"`؛ انتخاب فونت با دو `@import` در `fonts.css` (پیش‌فرض: IRANYekan + Pinar)؛ `font-display: swap`؛ fallback `"segoe ui", tahoma` |
| **Animation System** | WOW.js (فقط دسکتاپ + کلاس `wow-animation` روی html)، انیمیشن کپشن Swiper (`fadeInUpSmall` + delay)، CSS transitions (`.33s all ease` غالب، `.4s` پنل‌ها، `.7s` فلیپ)، keyframes: fadeIn/Up/Down/Left/Right(+Small)، slideIn*, cssload, donut |
| **Responsive Strategy** | Mobile-first، ۶ بریک‌پوینت BS (576/768/992/1200/1400)، سوییچ Navbar در **992px**، Hero بر پایه‌ی `vh`، ستون‌ها `col-sm-6 col-lg-4 col-xl-3`، `row-30/50` برای ریتم عمودی، **body 14px موبایل / 13px دسکتاپ** |
| **Component Strategy** | «Section → container → heading-decorated → row → col → article.component»؛ کامپوننت‌های مستقل با BEM ناقص؛ پیکربندی JS با data-attribute؛ variantها با کلاس modifier؛ **بدون partial** – هدر/فوتر در ۵۰ فایل کپی شده |

---

## ۳. Libraries – تصمیم نگه‌داری / جایگزینی / حذف

| کتابخانه (نسخه) | نقش در مرجع | تصمیم برای Nexora | دلیل |
|---|---|---|---|
| jQuery 3.2.1 + Migrate | پایه‌ی همه‌ی پلاگین‌ها | ❌ حذف | همه‌ی نیازها (toggle، drawer، tabs، fetch، state) با DOM API مدرن ۲۰ خطی حل می‌شود؛ ۸۷KB + وابستگی زنجیره‌ای حذف می‌شود |
| Bootstrap 4.0.0-beta2 (CSS) | Grid/Utilities | 🔁 **ارتقا به Bootstrap 5.3 – فقط Grid + Utilities (نسخه‌ی RTL)** | DNA لی‌اوت مرجع (۱۲ ستون، container 1200/1400، xxl) حفظ می‌شود؛ BS5 پشتیبانی رسمی RTL دارد؛ نسخه‌ی beta2 ناپایدار و بدون پشتیبانی است؛ بدون Bootstrap JS |
| Popper + Bootstrap JS (tab/collapse/tooltip/modal) | تب‌ها، آکاردئون | ❌ حذف | با `<details>`, `<dialog>` بومی و ۴۰ خط JS و `aria-*` صحیح پیاده می‌شود |
| **Swiper 3.4.2** | Hero slider | ✅ **نگه‌داری → Swiper 11** | بهترین اسلایدر touch/RTL؛ Hero، کاروسل محصولات، گالری محصول (thumbs)، برندها |
| Owl Carousel 2.2.1 / Slick 1.6.0 | کاروسل اخبار/نظرات | ❌ حذف (ادغام در Swiper) | سه کتابخانه‌ی اسلایدر موازی = ۱۰۰KB اضافه؛ Swiper همه را پوشش می‌دهد |
| **PhotoSwipe 4.1.1** | گالری پروژه | ✅ **نگه‌داری → PhotoSwipe 5** | «Image Zoom» صفحه‌ی محصول (pinch-zoom، keyboard، a11y) – نوشتن آن از صفر توجیه ندارد |
| Isotope 2.2.2 + Masonry | پنل دمو، بلاگ masonry، فیلتر پروژه | ❌ حذف | فیلتر فروشگاه نیاز به لی‌اوت انیمیشنی ندارد؛ CSS Grid + فیلتر داده‌ای در JS |
| Magnific Popup | لایت‌باکس ویدیو | ❌ حذف | PhotoSwipe + `<dialog>` کافی است |
| RD Navbar 2.2.5 | ناوبار responsive | ❌ بازنویسی سبک | نسخه‌ی جدید: header سه‌لایه + مگامنو + drawer با ARIA و focus-trap (~۱۵۰ خط) |
| Regula + jQuery Form + RDInputLabel | اعتبارسنجی/ارسال فرم | ❌ جایگزین با Constraint Validation API | پیام‌های خطای inline، `aria-invalid`, `aria-describedby`؛ بدون بک‌اند PHP (فرم‌های دمو) |
| WOW 1.1.3 | reveal در اسکرول | ❌ جایگزین با IntersectionObserver | ۱۵ خط، بدون وابستگی، احترام به `prefers-reduced-motion` |
| Select2 4.0.2 | سلکت‌های سفارشی | ❌ حذف | `<select>` بومی با استایل سفارشی (a11y بهتر، موبایل بهتر) |
| jQuery Countdown / TimeCircles | شمارش معکوس | ❌ جایگزین با ماژول ۴۰ خطی | Flash Sale countdown با `requestAnimationFrame`/`setInterval` |
| Stepper 3.0.8 | input[type=number] | ❌ جایگزین با کامپوننت `qty-stepper` | دکمه‌های +/- واقعی، min/max، a11y |
| CountTo / circle-progress / progress-linear | شمارنده‌ها | ❌ حذف | برای فروشگاه کاربرد ندارد (به‌جز نوار «X عدد باقی‌مانده» در Flash Sale که CSS ساده است) |
| RD Google Map, RD Audio, RD Calendar, Moment, Datetimepicker, Material Parallax, TouchSwipe, Device.js, Cookie, mousewheel, UIToTop, Easing, html5shiv | ویژگی‌های شرکتی/دمو | ❌ حذف | خارج از دامنه‌ی فروشگاه؛ IE پشتیبانی نمی‌شود؛ Back-to-top با ۱۰ خط |
| Font Awesome 4.7 | آیکون‌های اجتماعی | ❌ جایگزین با اسپرایت SVG کوچک | ۷۷KB فونت برای ۶ گلیف؛ FA4 آیکون X/WhatsApp/Aparat ندارد |
| MDI 1.4.57 | یک آیکون | ❌ حذف | ۸۰KB برای `mdi-dots-horizontal` (در Linearicons `ellipsis` موجود است) |
| **Linearicons** | سیستم اصلی آیکون | ✅ **نگه‌داری (subset شده)** | بریف: «اگر قالب اصلی Icon Library مشخصی دارد همان را حفظ کن»؛ همه‌ی گلیف‌های فروشگاهی را دارد (cart, cart-add, bag, heart, star/half/empty, magnifier, user, eye, truck, shield, gift, tag, percent, credit-card, funnel, grid, list, trash, sync, chevrons…) |
| فونت‌های فارسی (۲۵ خانواده) | تایپوگرافی | ✅ نگه‌داری **۲ خانواده‌ی پیش‌فرض** + حفظ مکانیزم alias | IRANYekan + Pinar (۸ فایل woff2 ≈ ۴۲۰KB)؛ ۲۳ خانواده‌ی دیگر (۱۳MB) حذف؛ تعویض فونت همچنان با تغییر یک `@import` |

**نتیجه‌ی وزنی:** JS از ~۸۵۷KB (core+script) به ≈ **۱۵۰KB** (Swiper ~۱۵۰KB minified قبل از gzip + PhotoSwipe ~۴۰KB + کد خودمان ~۳۰KB) و CSS از ~۸۰۰KB به ≈ **۱۲۰KB** می‌رسد.

---

## ۴. Design System استخراج‌شده (مرحله ۳)

### ۴.۱ رنگ‌ها

| نقش | مقدار در مرجع | تکرار | نقش در Nexora (توکن) | یادداشت |
|---|---|---|---|---|
| Primary / Accent | `#f6d014` | ۱۶۹× | `--color-primary` | هویت برند؛ حفظ می‌شود |
| Primary hover | `#000` (دکمه‌ها) / `#e3be09` | – | `--color-primary-hover: #e3be09`، دکمه‌ی primary → مشکی | همان الگوی «زرد → مشکی» |
| Primary tints | `rgba(246,208,20,.1/.16/.66/.9)`, `#fae476`, `#f8da45` | – | `--color-primary-soft: rgba(246,208,20,.12)` | برای بج/هاور ملایم |
| Dark 900 | `#000` | ۱۲۰× | `--color-black` | دکمه‌ی hover، متن روی زرد |
| Dark 800 | `#111111` (footer) | – | `--color-gray-900` | فوتر پایینی |
| Dark 700 | `#151515` (header/pre-footer/snackbar) | ۱۷× | `--color-gray-800` | سطوح تیره |
| Text | `#333` | ۵۶× | `--color-text: #333` | تیتر و متن اصلی |
| Text muted | `#888` | ۴۹× | `--color-text-muted: #6b6b6b` ⚠️ | `#888` روی سفید = ۳٫۵:۱ (رد AA) → تیره‌تر می‌شود |
| Gray 400 | `#c7c7c7` | – | `--color-gray-400` | آیکون‌های غیرفعال |
| Border | `#e0e0e2` (hr), `#ececee` (input focus/pagination) | ۱۳× / ۴۹× | `--color-border: #e6e6e8`، `--color-border-strong: #d9d9d9` | |
| Surface | `#f8f8f8` (input, gray-lighter), `#f3f3f3`, `#fbfbfb` (dropdown) | ۲۷× | `--color-surface: #f8f8f8`, `--color-surface-2: #fbfbfb` | بخش‌های متناوب |
| Background | `#fff` | ۱۸۹× | `--color-bg` | |
| Danger / Success / Warning | `#d9534f`, `#67b168`, `#c0a16b` (BS3 legacy) | – | `--color-danger: #d64545`, `--color-success: #2e9e5b`, `--color-warning: #d9a400`, `--color-info: #2f6fed` | برای موجودی/خطا/تخفیف |
| Overlay | `rgba(21,21,21,.5)`, `rgba(0,0,0,.5)` | ۱۸× | `--color-overlay` | hover تصاویر |

> ⚠️ **نکته‌ی کنتراست:** مرجع متن **سفید روی زرد** می‌گذارد (`.button-primary`, `.bg-accent`) → نسبت کنتراست ≈ **۱٫۵:۱** (رد کامل WCAG). در Nexora متن روی زرد **`#111`** می‌شود (≈ ۱۲:۱). این تنها انحراف عمدی از پالت مرجع است.

### ۴.۲ تایپوگرافی

| عنصر | مرجع | Nexora |
|---|---|---|
| Body font | `"primary-font"` = IRANYekan 300/400/500/700 | همان (alias حفظ می‌شود) |
| Heading font | `"secondary-font"` = Pinar 700 | همان – فقط برای h1/h2/تیتر بخش؛ h4–h6/تیتر محصول با IRANYekan 700 (خوانایی در اندازه‌ی کوچک) |
| Body size | 14px موبایل → **13px دسکتاپ** (≥992) | `--fs-base: 14px` ثابت (۱۳px برای فروشگاه کوچک است)، `--fs-sm: 13px`، `--fs-xs: 12px` |
| Line-height | body **2** ، headings **1.85** | body `1.9`، headings `1.5`، قیمت/عدد `1.2` |
| Weight | 400 body، 700 headings، h5 = 400 | 400/500/700 |
| h1 | 30 → 56 (768) → 74 (1200) → 70 (1400) | `clamp(28px, 4vw, 56px)` – Hero |
| h2 | 26 → 32 → 44 → 60 | `clamp(22px, 2.6vw, 32px)` – تیتر بخش (در مرجع تیتر بخش h4 است؛ برای SEO به h2 منتقل می‌شود با همان اندازه‌ی بصری 18/24/30) |
| h3 | 22 → 28 → 40 | `clamp(20px, 2vw, 26px)` |
| h4 | 18 → 24 → 30 | 18 → 20 |
| h5 / h6 | 16 → 20 → 18 / 14 → 16 | 16 / 14 |
| Heading decoration | `::after` خط `1px × 35→50px` زرد، `margin-top: 15px`، وسط‌چین در `.text-center` | حفظ می‌شود: `.section-title::after` (امضای بصری قالب) |
| small | 14 → 16 | 12–13 |
| Link | `#f6d014` → hover `#000`، `transition .33s` | لینک متنی `#333` → hover primary؛ لینک داخل تیتر inherit → hover primary |

### ۴.۳ فاصله‌گذاری، شبکه و کانتینر

| توکن | مرجع | Nexora |
|---|---|---|
| Container | 540 / 720 / 960 / **1200** / **1400** (+ `padding 15px`) | همان (BS5: `--bs-gutter-x: 30px`) + `container-fluid` برای Hero |
| Grid | 12 ستون، gutter 30px | همان |
| Vertical rhythm rows | `row-30/40/50/60` (60 = 50!) | `--space-*` مقیاس 4px: 4, 8, 12, 16, 20, 24, 30, 40, 50, 60, 80 |
| Section padding | xs/sm: 35 → 50/60؛ md: 60 → 70 → 80/100؛ lg: 60 → 80 → 100 → 120؛ xl: 60 → 95 → 130 → 190 | `.section` = `clamp(40px, 6vw, 80px)`؛ `.section--sm` = `clamp(24px, 4vw, 50px)`؛ `.section--lg` = `clamp(56px, 8vw, 110px)` |
| Media-object | `.unit` با spacing xs/sm/md (15/20/30) | flex + gap |
| Lists | `list-xxs 11px`, `xs 16px`, `sm 15px`, `md 30px` | `--space-2/3/4/6` |

### ۴.۴ شکل، سایه، حرکت

| توکن | مرجع | Nexora |
|---|---|---|
| Radius | دکمه/اینپوت **0**؛ pagination **4px**؛ تگ‌ها 3px؛ آواتار 50%؛ نادر 8px | `--radius-none: 0` (دکمه‌ی اصلی – امضای صنعتی)، `--radius-sm: 3px` (بج، input)، `--radius-md: 4px` (کارت)، `--radius-pill: 999px` (بج تخفیف/چیپ)، `--radius-full: 50%` |
| Shadow | کارت: `0 2px 12px rgba(136,136,136,.1)`؛ کوچک: `0 1px 4px rgba(0,0,0,.15)`؛ برجسته: `0 5px 23px rgba(0,0,0,.15)`؛ دراپ‌داون: `0 1px 10px rgba(0,0,0,.15)` | `--shadow-sm / --shadow-md / --shadow-lg` = همان سه مقدار |
| Transition | `.3s / .33s ease` غالب (۲۴۸×)، `.4s` پنل‌ها، `.2s/.15s` میکرو، `.7s cubic-bezier(.4,.2,.2,1)` فلیپ | `--dur-fast: 150ms`, `--dur-base: 300ms`, `--dur-slow: 450ms`, `--ease: cubic-bezier(.4,.2,.2,1)`؛ `prefers-reduced-motion` |
| Border | `1px solid #e0e0e2 / #ececee` | `1px solid var(--color-border)` |

### ۴.۵ دکمه‌ها

| ویژگی | مرجع | Nexora |
|---|---|---|
| Base | `inline-block; border:1px solid; radius 0; padding 12px 25px; 14px/17px; weight 400; transition .33s` | `.btn` = `inline-flex; gap 8px; min-height 44px; padding 0 24px; 14px; weight 500; radius 0` |
| Primary | زرد + متن سفید → hover مشکی | زرد + متن `#111` → hover مشکی + متن سفید |
| Default/Outline | شفاف + border `#888` + متن `#151515` → hover مشکی پر | `.btn--outline` همان |
| Black | مشکی → hover زرد | `.btn--dark` همان (Buy Now) |
| Link | زیرخط، 12px، زرد → hover مشکی | `.btn--link` |
| Sizes | xs 5×25/12، sm 6×25/13، lg 18×45/16، xl 20×50/18 | `--sm (36px)`, `--lg (52px)`, `--icon (44×44)` |
| Icon button | `.button-icon .icon` 1.85em | `.btn--icon` مربع 44px (touch target) |

### ۴.۶ فرم‌ها

| ویژگی | مرجع | Nexora |
|---|---|---|
| Input | `min-height 45px; padding 11px 19px; 13px; color #888; bg #f8f8f8; border 1px #f8f8f8; radius 0` | `min-height 46px; padding 0 16px; 14px; color text; bg surface; border 1px border; radius 3px` |
| Focus | `bg #fff; border #ececee` | `bg #fff; border primary; box-shadow 0 0 0 3px primary-soft` (focus قابل‌دیدن) |
| Label | Floating (`RDInputLabel`) | Label ثابت بالای فیلد (خوانایی فارسی و a11y بهتر) + placeholder اختیاری |
| Checkbox/Radio | `checkbox-custom-dummy` زرد | `accent-color: var(--color-primary)` + استایل سفارشی |
| Validation | Regula → snackbar | inline error + `aria-invalid` + خلاصه‌ی خطا |

### ۴.۷ ناوبری

| ویژگی | مرجع | Nexora |
|---|---|---|
| ساختار | Top bar (dark) + Brand/Search + Nav | **Announcement bar** (زرد) + **Top bar** (dark: تماس/پیگیری سفارش/زبان) + **Main bar** (لوگو، جستجوی بزرگ، اکشن‌ها: حساب/علاقه‌مندی/سبد با شمارنده) + **Nav bar** (دسته‌ها با مگامنو + لینک‌ها) |
| ارتفاع | 100px → sticky 74px | 72px main + 48px nav → sticky: فقط main 64px |
| لینک | `#000` → hover/active زرد، caret FA | همان + underline زرد ۲px انیمیشنی |
| Dropdown | 216px، padding 30px، bg `#fbfbfb`، سایه | همان زبان بصری، `radius 4`، انیمیشن fade+translateY 8px، باز شدن با hover و focus/keyboard |
| Mobile | پنل 56px + off-canvas 280px از راست | header فشرده 60px + drawer 300px از راست با overlay، focus-trap، `Esc` |

### ۴.۸ آیکون‌ها

| اندازه | مرجع | Nexora |
|---|---|---|
| xxs / sm / md / lg / xxl | 15 / 20 / 30 / 44 / 48→80 | `--icon-xs: 16px`, `--icon-sm: 20px`, `--icon-md: 24px`, `--icon-lg: 32px`, `--icon-xl: 48px` |
| رنگ‌ها | default `#333`, gray `#888`, primary زرد, gray-4 `#c7c7c7` | inherit `currentColor` |
| Circle | `2.2em` دایره | `.icon-btn` 44px دایره/مربع |

---

## ۵. فلسفه‌ی طراحی قالب مرجع (مرحله ۴)

1. **Visual Hierarchy:** یک رنگ اکسنت پرانرژی (زرد) روی پالت بی‌رنگ (سفید/خاکستری/مشکی). زرد فقط برای «نقاط عمل» استفاده می‌شود: دکمه‌ی اصلی، خط زیر تیتر، آیکون‌ها، لینک hover، یک بخش تمام‌زرد (شمارنده/CTA). این انضباط باعث می‌شود چشم دقیقاً به CTA برود → در فروشگاه: **Add to Cart، قیمت تخفیف، بج فروش ویژه، خط تیتر**.
2. **Visual Rhythm:** بخش‌ها با پس‌زمینه‌ی متناوب `سفید → #f8f8f8 → زرد → تصویر تیره → سفید` جدا می‌شوند، نه با خط. هر بخش یک تیتر کوتاه + خط زرد + گرید. در فروشگاه همین ریتم: `Hero → دسته‌ها (سفید) → Flash Sale (تیره/زرد) → محصولات (سفید) → بنر (تصویر) → …`
3. **Whitespace & Density:** padding بخش‌ها ۶۰–۱۲۰px، gutter 30px، line-height 2؛ تراکم پایین و «تنفس» زیاد. فروشگاه به تراکم بیشتری نیاز دارد (کارت‌های محصول)، پس: padding بخش‌ها کمی کمتر (۴۰–۸۰px)، اما gutter و خط تیتر حفظ می‌شود.
4. **Alignment:** تیترهای بخش وسط‌چین (`text-center`) اما محتوای split (`object-wrap`) راست‌چین. کارت‌ها همیشه هم‌ارتفاع (flex). در فروشگاه: تیتر بخش **راست‌چین با لینک «مشاهده همه» در چپ** (الگوی استاندارد فروشگاهی) و فقط بخش‌های تبلیغاتی وسط‌چین.
5. **Card Composition:** تصویر با نسبت ثابت (4:3 یا 480×361) → عنوان (h6) → متن → لینک زیرخط‌دار. hover = overlay تیره + آیکون یا فلیپ سه‌بعدی. در فروشگاه: تصویر **1:1** → دسته (muted) → نام → امتیاز → قیمت → دکمه؛ hover = تصویر دوم + اکشن‌های شناور (علاقه‌مندی/مشاهده‌ی سریع/مقایسه) + دکمه‌ی افزودن که از پایین می‌آید.
6. **CTA Placement:** یک CTA اصلی در Hero، نوار CTA بلافاصله بعد از Hero، CTA میانی روی زرد، فرم تماس قبل از فوتر. در فروشگاه: Hero CTA، نوار «ارسال رایگان/ضمانت/بازگشت» بعد از Hero (Trust bar)، بنر تبلیغاتی میانی، خبرنامه قبل از فوتر.
7. **Navigation Pattern:** سه لایه (اطلاعات تماس / برند / منو) + مگامنو + جستجوی جمع‌شونده + sticky با ارتفاع کمتر. در فروشگاه جستجو **همیشه نمایان و بزرگ** می‌شود (اصلی‌ترین ابزار کاربر فروشگاه).
8. **Mobile UX:** off-canvas از راست، Hero کوتاه‌تر (80vh)، ستون‌ها 1→2→3→4. در فروشگاه: bottom action bar در صفحه‌ی محصول (قیمت + افزودن)، Filter drawer، گرید 2 ستونه از 360px.
9. **Interaction Design:** transitions ملایم ۰٫۳s، hover قابل‌پیش‌بینی (رنگ ← زرد)، انیمیشن ورود کپشن Hero. بدون افکت اضافه. همین ذائقه در Nexora حفظ می‌شود.

### ضعف‌های مرجع که در Nexora اصلاح می‌شوند
* `outline: none !important` سراسری → focus ring نامرئی.
* `maximum-scale=1, user-scalable=0` در viewport → ممنوعیت زوم (رد WCAG 1.4.4).
* تیتر بخش‌ها `h4`، تیتر کارت‌ها `h6`/`p.heading-6` → سلسله‌مراتب SEO خراب.
* `alt=""` روی همه‌ی تصاویر محتوایی، `div` به‌عنوان toggle، لینک‌های آیکونی بدون label.
* ۸۰۶KB JS + ۸۰۰KB CSS + ۱۴MB فونت برای هر صفحه؛ TTF فشرده‌نشده‌ی Linearicons (۴۹۸KB).
* هدر/فوتر در ۵۰ فایل کپی شده؛ رنگ‌ها ۱۶۹ بار hard-code.
* بدون meta description / Open Graph / structured data / canonical.
* متن سفید روی زرد (کنتراست ۱٫۵:۱).

---

## ۶. Component Architecture پیشنهادی (Nexora)

قرارداد نام‌گذاری: **BEM** (`block__element--modifier`) + state با `is-*` / `aria-*` + utility با `u-*`. همه‌ی مقادیر از **CSS Custom Properties** (`assets/css/tokens.css`).

### لایه‌بندی CSS (ترتیب بارگذاری)
```
vendor/bootstrap-grid.rtl.min.css   ← فقط grid + flex/spacing utilities
vendor/swiper-bundle.min.css
vendor/photoswipe.css
css/tokens.css        ← :root متغیرها (رنگ، فونت، فاصله، radius، سایه، حرکت، z-index)
css/base.css          ← reset، تایپوگرافی، لینک، focus-visible، تصاویر، فرم‌های بومی
css/layout.css        ← .section, .section-title, .page-header, .site-footer, .sidebar-layout, .breadcrumb
css/components.css    ← همه‌ی بلاک‌های زیر
css/pages.css         ← استایل‌های اختصاصی هر صفحه (product, checkout, account…)
```

### فهرست بلاک‌ها

| بلاک | عناصر / modifierها | State ها | JS |
|---|---|---|---|
| `announcement` | `__text`, `__close` | dismissed (localStorage) | ✔ |
| `topbar` | `__list`, `__item` | – | – |
| `header` | `__brand`, `__search`, `__actions`, `__action` (+`__badge` شمارنده), `--sticky` | `is-stuck` | ✔ (IntersectionObserver sentinel) |
| `nav` | `__list`, `__item`, `__link`, `__caret`, `__dropdown`, `__megamenu`, `__col`, `__heading` | `is-open`, `aria-expanded` | ✔ hover + click + keyboard |
| `drawer` | `__panel`, `__header`, `__body`, `__close`, `--right`, `--filters` | `is-open`, `inert` روی صفحه | ✔ focus-trap, Esc |
| `search` | `__input`, `__submit`, `__suggest` | `is-active` | ✔ (پیشنهاد ساده از دیتا) |
| `hero` | `__slide`, `__media`, `__content`, `__eyebrow`, `__title`, `__text`, `__cta` | – | ✔ Swiper (autoplay 5000, loop, fade) |
| `trust-bar` | `__item`, `__icon`, `__title`, `__text` | – | – |
| `section-title` | `__heading`, `__link`, `--center` | – | – |
| `category-card` | `__media`, `__name`, `__count`, `--tile`, `--round` | – | – |
| **`product-card`** | `__media`, `__img`, `__img--hover`, `__badges`, `__actions`, `__action`, `__body`, `__category`, `__title`, `__rating`, `__price`, `__add`, `--list`, `--skeleton` | `is-in-wishlist`, `is-in-cart`, `is-loading`, `is-out-of-stock` | ✔ رندر از یک تابع `renderProductCard()` |
| `badge` | `--sale`, `--new`, `--hot`, `--out`, `--discount` | – | – |
| `price` | `__current`, `__old`, `__discount`, `__currency` | – | – |
| `rating` | `__stars`, `__star` (full/half/empty), `__value`, `__count`, `--input` | – | ✔ (input در فرم نقد) |
| `countdown` | `__unit`, `__value`, `__label` | `is-expired` | ✔ |
| `promo-banner` | `__media`, `__content`, `--wide`, `--split` | – | – |
| `brand-strip` | `__logo` | – | ✔ Swiper (autoplay) |
| `review-card` | `__avatar`, `__text`, `__author`, `__rating` | – | ✔ Swiper |
| `post-card` | `__media`, `__meta`, `__title`, `__excerpt`, `__more` | – | – |
| `newsletter` | `__form`, `__input`, `__submit` | `is-success` | ✔ validation |
| `footer` | `__grid`, `__col`, `__heading`, `__list`, `__contact`, `__bottom`, `__payments` | – | – |
| `breadcrumb` | `__item`, `__sep` | `aria-current` | – |
| `filters` | `__group`, `__title`, `__body`, `__list`, `__checkbox`, `__price-range`, `__chips`, `__clear` | `is-collapsed` | ✔ فیلتر داده‌ای + URL params |
| `toolbar` | `__count`, `__sort`, `__view`, `__filter-btn` | `is-grid`/`is-list` | ✔ |
| `pagination` | `__item`, `__link`, `__prev`, `__next` | `is-active`, `is-disabled` | ✔ |
| `gallery` | `__main`, `__thumbs`, `__zoom`, `__badge` | – | ✔ Swiper thumbs + PhotoSwipe |
| `variants` | `__group`, `__label`, `__swatch` (color), `__chip` (size) | `is-selected`, `is-disabled` | ✔ |
| `qty` | `__minus`, `__input`, `__plus` | min/max | ✔ |
| `stock` | `--in`, `--low`, `--out` | – | – |
| `tabs` | `__list`, `__tab`, `__panel` | `aria-selected` | ✔ روving tabindex |
| `accordion` | `<details>` + `__summary`, `__body` | `open` | – |
| `spec-table` | `__row`, `__key`, `__val` | – | – |
| `cart-table` / `cart-item` | `__media`, `__info`, `__qty`, `__total`, `__remove` | – | ✔ localStorage |
| `summary` | `__row`, `__total`, `__coupon`, `__cta` | – | ✔ |
| `checkout-steps` | `__step` | `is-active`, `is-done` | – |
| `form` | `__group`, `__label`, `__control`, `__hint`, `__error`, `__row` | `is-invalid` | ✔ Constraint Validation |
| `option-card` | (روش ارسال/پرداخت) `__radio`, `__title`, `__meta` | `is-selected` | – |
| `auth-card` | `__header`, `__body`, `__social`, `__footer` | – | ✔ |
| `account` | `__nav`, `__panel`, `__stat` | `is-active` | – |
| `order-row` / `order-timeline` | `__status` | – | – |
| `address-card` | `__default`, `__actions` | – | – |
| `modal` (`<dialog>`) | `__panel`, `__close`, `--quick-view` | `open` | ✔ |
| `toast` | `__icon`, `__text`, `--success/--error` | auto-dismiss | ✔ |
| `skeleton` | `--text`, `--media`, `--card` | – | ✔ |
| `empty-state` | `__icon`, `__title`, `__text`, `__cta` | – | – |
| `back-to-top` | – | `is-visible` | ✔ |

### ماژول‌های JS (ES Modules، بدون فریم‌ورک)
```
assets/js/
├── main.js                ← bootstrap: init همه‌ی ماژول‌ها بر اساس data-attribute (الگوی مرجع)
├── data/products.js       ← کاتالوگ نمونه (۲۴–۳۲ محصول، دسته‌ها، برندها)
├── store/state.js         ← cart / wishlist / compare در localStorage + رویدادها
├── components/
│   ├── header.js (sticky, search, drawer, megamenu)
│   ├── product-card.js (render + add to cart/wishlist animation)
│   ├── sliders.js (Swiper configs: hero, products, brands, reviews, gallery)
│   ├── countdown.js, tabs.js, modal.js, toast.js, qty.js, forms.js,
│   ├── filters.js (shop/search), pagination.js, gallery.js (PhotoSwipe), reveal.js
└── pages/ (shop.js, product.js, cart.js, checkout.js, account.js, wishlist.js, search.js)
```

---

## ۷. E-Commerce Page Architecture

| صفحه | بخش‌ها (به ترتیب) |
|---|---|
| **index.html** | Announcement bar → Top bar → Header (لوگو، جستجو، اکشن‌ها) → Nav (دسته‌ها/مگامنو) → Hero slider (3 اسلاید) → Trust bar (۴ آیتم) → Featured Categories (6–8 کاشی) → Flash Sale (countdown + کاروسل محصول) → Featured Products (تب‌های: پرفروش/جدید/تخفیف) → Promo Banner (۲ بنر split) → New Arrivals (گرید 4×2) → Best Sellers (کاروسل) → Product Collections (۳ ستون لیست کوچک: پرفروش/پربازدید/بیشترین امتیاز) → Brands (کاروسل لوگو) → Customer Reviews → Blog (۳ کارت) → Newsletter → Footer |
| **shop.html** | Breadcrumb + تیتر → Sidebar Filters (دسته‌ها با شمارنده، محدوده‌ی قیمت dual-range، برند با جستجو، امتیاز، رنگ، موجودی) + Chips فیلترهای فعال → Toolbar (تعداد نتایج، مرتب‌سازی، Grid/List، دکمه‌ی فیلتر موبایل) → Product Grid (4/3/2 ستون) یا List → Pagination → Footer؛ Filter Drawer < 992px |
| **product.html** | Breadcrumb → Gallery (Swiper اصلی + thumbs عمودی/افقی، zoom با PhotoSwipe، بج) → Info (دسته، h1، امتیاز + تعداد نقد + لینک، قیمت/قدیم/درصد، وضعیت موجودی، ویژگی‌های کلیدی، Variants رنگ/سایز، Qty + Add to Cart + Buy Now، Wishlist/Compare/Share، اطمینان: ارسال/ضمانت/بازگشت) → Tabs (توضیحات، مشخصات فنی جدول، اطلاعات ارسال، نقدها با نمودار امتیاز و فرم) → Related Products (کاروسل) → Recently viewed → Footer؛ نوار چسبان پایین در موبایل؛ **JSON-LD Product** |
| **cart.html** | Breadcrumb → جدول آیتم‌ها (تصویر، نام+ویژگی، قیمت، Qty، جمع، حذف) → Coupon + «ادامه‌ی خرید» → Summary (جمع جزء، ارسال با گزینه، تخفیف، مالیات، جمع کل، CTA) → Empty state → پیشنهاد محصولات → Footer |
| **checkout.html** | Steps (سبد → اطلاعات → پرداخت) → فرم اطلاعات مشتری (نام، موبایل، ایمیل) → آدرس (استان/شهر select، آدرس، کدپستی، ذخیره) → روش ارسال (option-cards) → روش پرداخت (درگاه/کارت‌به‌کارت/در محل) → یادداشت سفارش → Order Summary چسبان (آیتم‌ها، کوپن، جمع) → پذیرش قوانین → Place Order |
| **wishlist.html** | Breadcrumb → جدول/گرید علاقه‌مندی‌ها (تصویر، نام، قیمت، موجودی، Add to Cart، حذف) → «افزودن همه به سبد» → Empty state |
| **search.html** | Search hero (input بزرگ + پیشنهاد/محبوب‌ها) → نتایج (تعداد، تب: محصولات/مقالات) → فیلترهای سبک (chips) → گرید کارت محصول → «نتیجه‌ای یافت نشد» state → Pagination |
| **login.html / register.html / forgot-password.html** | لی‌اوت split (فرم + تصویر/مزایا) → auth-card با validation، نمایش/مخفی‌کردن رمز، «مرا به خاطر بسپار»، ورود با شبکه‌های اجتماعی، لینک‌های متقابل |
| **account.html** (+ `account-orders.html`, `account-order.html`, `account-addresses.html`, `account-profile.html`) | Sidebar حساب (آواتار، منو: داشبورد/سفارش‌ها/علاقه‌مندی/آدرس‌ها/پروفایل/خروج) → Dashboard (کارت‌های آمار، آخرین سفارش‌ها) → Orders (جدول با وضعیت رنگی) → Order details (timeline وضعیت، آیتم‌ها، آدرس، فاکتور) → Addresses (کارت‌ها + فرم) → Profile (فرم + تغییر رمز) |
| **blog.html / single-post.html** | Blog grid (3 ستون) + Sidebar (جستجو، دسته‌ها، پست‌های اخیر، تگ‌ها) → Pagination؛ Single: تصویر شاخص، meta، محتوای تایپوگرافی، تگ‌ها، اشتراک‌گذاری، نویسنده، مقالات مرتبط، نظرات |
| **404.html** | (اضافه – از مرجع) عدد بزرگ، پیام، جستجو، لینک‌های پرکاربرد |

---

## ۸. Responsive Strategy

| بریک‌پوینت | Header | Product grid | Sidebar / Filters | Hero | Typography |
|---|---|---|---|---|---|
| ≥ 1440 (xxl 1400) | کامل ۳ لایه، مگامنو تمام‌عرض کانتینر 1400 | 4 ستون (Shop) / 5 ستون (Home carousel) | Sidebar 25% | 560px | base 14 |
| 1200–1399 (xl) | کامل | 4 | Sidebar 25% | 520px | – |
| 992–1199 (lg) | کامل، جستجو باریک‌تر | 3 | Sidebar 30% | 480px | h1 clamp |
| 768–991 (md) | **جمع‌شده**: لوگو + آیکون‌ها + همبرگر؛ جستجو خط دوم | 3 → 2 | **Filter Drawer** | 420px | – |
| 576–767 (sm) | فشرده 60px | 2 | Drawer | 380px (نسبت 4:3) | h2 22 |
| 480–575 | فشرده | 2 (gutter 12px) | Drawer | 360px | – |
| < 480 | فشرده، اکشن‌ها فقط سبد/منو | 2 (کارت فشرده: دکمه‌ی افزودن آیکونی) / 1 در List | Drawer تمام‌عرض | 320px | base 14، دکمه‌ها full-width |

اصول: mobile-first، `clamp()` برای تایپ و padding، touch target ≥ 44px، نسبت تصاویر ثابت با `aspect-ratio` (محصول 1:1، بلاگ 4:3، بنر 16:9 / 21:9، دسته 1:1)، `loading="lazy"` + `srcset/sizes`، نوار اکشن چسبان پایین در صفحه‌ی محصول موبایل، `hover` فقط با `@media (hover: hover)` (اکشن‌های کارت در موبایل همیشه نمایان)، `prefers-reduced-motion`.

---

## ۹. Icon Strategy

* **سیستم اصلی: Linearicons** (حفظ سیستم مرجع) – فایل TTF ۴۹۸KB با `pyftsubset` به **≈ ۸۰ گلیف مورد نیاز** (woff2 ≈ ۱۰–۱۵KB) کاهش می‌یابد؛ کلاس‌ها همان `linear-icon-*` می‌ماند تا با مرجع سازگار باشد.
* نگاشت آیکون‌های فروشگاهی: `cart / cart-add / cart-full` (سبد)، `heart` (علاقه‌مندی)، `sync` (مقایسه)، `eye` (مشاهده‌ی سریع)، `magnifier` (جستجو)، `user` (حساب)، `star / star-half / star-empty` (امتیاز)، `truck` (ارسال)، `shield` (ضمانت)، `undo` (بازگشت کالا)، `headset` (پشتیبانی)، `tag / percent` (تخفیف)، `credit-card` (پرداخت)، `funnel` (فیلتر)، `grid / list` (نمای گرید/لیست)، `trash2` (حذف)، `plus / minus` (Qty)، `chevron-*` / `arrow-*` (ناوبری)، `cross / menu` (بستن/همبرگر)، `check` (موجود)، `alarm / hourglass` (Flash sale)، `map-marker / telephone / envelope` (تماس)، `share2`, `lock`, `gift`, `wallet`, `home`, `bag`, `bubble`, `pencil`, `exit`, `cog`, `box`, `history`.
* **برندها (اجتماعی/پرداخت): اسپرایت SVG inline** (`assets/icons/sprite.svg` + `<use href="#i-instagram">`) – ۸–۱۰ آیکون؛ حذف FA 4.7 و MDI.
* قواعد: اندازه‌ها فقط از توکن (`16/20/24/32/48`)، آیکون‌های تزئینی `aria-hidden="true"`، دکمه‌های آیکونی با `aria-label` و متن `visually-hidden`، رنگ از `currentColor`، hover همراه با والد (رنگ ← primary)، **بدون هیچ Emoji**.

---

## ۱۰. ساختار پیشنهادی پروژه

```
Nexora-shop/
├── index.html, shop.html, product.html, cart.html, checkout.html, wishlist.html,
│   search.html, login.html, register.html, forgot-password.html,
│   account.html, account-orders.html, account-order.html, account-addresses.html, account-profile.html,
│   blog.html, single-post.html, 404.html
├── assets/
│   ├── css/  tokens.css, base.css, layout.css, components.css, pages.css
│   ├── js/   main.js, data/, store/, components/, pages/
│   ├── vendor/  bootstrap-grid.rtl.min.css, swiper/, photoswipe/
│   ├── fonts/   iran-yekan-*.woff2, pinar-*.woff2, linearicons-subset.woff2 (+ css alias مثل مرجع)
│   ├── icons/   sprite.svg, favicon.svg, apple-touch-icon.png
│   └── img/     products/, categories/, banners/, brands/, blog/, avatars/ (تولید اختصاصی، بدون placeholder خاکستری)
├── docs/  01-reference-analysis.md (این سند)، 02-design-system.md، 03-components.md
└── README.md
```

* فایل‌های HTML در **ریشه** (مثل مرجع) – برای هاستینگ استاتیک/GitHub Pages `index.html` باید در ریشه باشد؛ مسیرها نسبی می‌ماند.
* بدون build step اجباری: پروژه با باز کردن `index.html` یا هر static server کار می‌کند. (اختیاری: `npm run build` برای minify.)
* SEO: `<title>` یکتا، `meta description`، Open Graph، `link rel=canonical`، `h1` یکتا، breadcrumb با `BreadcrumbList` JSON-LD، `Product` JSON-LD در product.html، `alt` معنادار، URLهای توصیفی (`product.html?slug=…` در دمو).

---

## ۱۱. برنامه‌ی پیاده‌سازی (فازها)

1. **Foundation:** tokens.css، base.css، فونت‌ها (subset)، Linearicons subset، اسپرایت SVG، vendorها (Swiper 11، PhotoSwipe 5، BS5 grid RTL)، layout (header/nav/drawer/footer) → یک صفحه‌ی اسکلت.
2. **Product Card + دیتا:** `products.js`، `renderProductCard()`، state سبد/علاقه‌مندی، toast، skeleton، انیمیشن افزودن به سبد.
3. **Home:** همه‌ی ۱۶ بخش + Swiperها + countdown.
4. **Shop + Search:** فیلترها (داده‌ای + URL)، toolbar، grid/list، pagination، drawer موبایل، empty state.
5. **Product:** گالری + zoom، variants، qty، tabs، نقدها، related، JSON-LD، نوار موبایل.
6. **Cart / Checkout / Wishlist:** جدول‌ها، summary، کوپن، فرم‌های چندمرحله‌ای با validation.
7. **Auth + Account (۵ صفحه) + Blog (۲ صفحه) + 404.**
8. **QA:** بازبینی در ۷ عرض هدف، کیبورد، کنتراست، Lighthouse، حذف کد مرده، README.

### تصمیم‌های باز (نیازمند تأیید)
1. **حوزه‌ی فروشگاه (niche)** – هویت زرد/مشکی مرجع طبیعی‌ترین تطابق را با «ابزار و تجهیزات / لوازم فنی» دارد، اما با «کالای دیجیتال» و «فروشگاه عمومی» هم کار می‌کند.
2. **زبان و جهت** – مرجع فارسی/RTL است؛ پیش‌فرض: فارسی RTL با logical properties (آماده‌ی LTR).
3. **استک** – پیشنهاد: Vanilla JS + Swiper 11 + PhotoSwipe 5 + Bootstrap 5 Grid (بدون jQuery). گزینه‌ی جایگزین: حفظ jQuery/Bootstrap 4 مرجع.
