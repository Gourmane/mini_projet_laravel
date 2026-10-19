# صور المنتجات — شرح بسيط

## كيف تعمل الميزة؟

كل Product عنده حقل واحد اسمه image_url في SQLite. الحقل nullable: يمكن تركه فارغاً. لا نحفظ ملفات الصور في Laravel؛ نحفظ عنوان URL فقط. المتصفح يطلب الصورة مباشرة من الموقع الذي يستضيفها.

Migration الجديدة هي backend/database/migrations/2026_10_18_000002_add_image_url_to_products_table.php. الأمر artisan migrate يضيف العمود إلى نفس قاعدة البيانات، ويحافظ على المنتجات والمخزون والحركات. لم يتم استعمال migrate:fresh.

Product.php يسمح بحفظ image_url عبر fillable. ProductRequest.php يقبل فقط URL صالحاً من نوع HTTP أو HTTPS، بحد أقصى 2048 حرفاً، أو null. نفس ProductController يستعمل validated() في POST وPATCH، وEloquent يرجع الحقل تلقائياً في JSON، في القائمة والتفاصيل وبعد الحفظ. لا توجد API جديدة للصور ولا upload ولا Base64 ولا image storage.

## كيف يغيّر Admin الصورة؟

1. افتح Produits ثم Voir détails.
2. اضغط Modifier.
3. ألصق رابط الصورة في URL de l’image. الأفضل رابط HTTPS مباشر للصورة.
4. يظهر Preview باستعمال useState وimg عادي.
5. اضغط Enregistrer le produit. Axios يرسل image_url إلى Laravel، ثم تظهر الصورة المحفوظة في التفاصيل والقائمة.
6. لمسح الصورة، اترك الحقل فارغاً واحفظ. يمكنك استعمال Annuler لإلغاء التعديلات.

Employee يرى الصور والمعلومات والحركات فقط. زر Modifier والفورم يظهران للـAdmin وحده. الحماية موجودة أيضاً في Laravel: محاولة PATCH أو POST من Employee ترجع 403، حتى لو حاول خارج React.

## ماذا يفعل React؟

Products.jsx يحتفظ بنفس search وfilters وpagination. يستقبل products.data من API ويستعمل map() لعرض ProductCard.jsx. كل Card فيها الصورة والاسم والماركة وSKU والفئة والثمن بـMAD والكمية وحالة المخزون ورابط Voir détails. رابط الرجوع يحتفظ بفلاتر القائمة.

ProductDetail.jsx هو نفس الملف الموجود سابقاً: أضفنا صورة أكبر ومعلومات واضحة وزر Modifier والفورم الحالي مع image_url. بقيت روابط الحركة وسجل الحركات. لا يتغير stock داخل فورم المنتج.

في UI.jsx توجد دالتان صغيرتان مشتركتان: productImage تختار الرابط أو /product-placeholder.svg، وimageFallback تغيّر src إلى هذه الصورة المحلية عند onError. كل الصور لها alt، وصور القائمة تستعمل loading="lazy". CSS Grid يعرض 4 كروت على الشاشة الكبيرة، 3 على الشاشة المتوسطة، 2 على Tablet و1 على Mobile. لم نضف dependencies.

## Seeders والبيانات القديمة

كل الروابط مكتوبة مباشرة وواضحة داخل backend/database/seeders/ProductImageSeeder.php، بجانب SKU واسم المنتج. هذا الملف يحدّث image_url فقط، حسب SKU، وللصور الفارغة فقط. لا ينشئ منتجات أو حركات، ولا يغيّر quantity أو category أو price أو timestamps. إعادة تشغيله آمنة، والرابط غير الفارغ الذي اختاره Admin يبقى كما هو. إذا مسح Admin الصورة ثم شغّل هذا Seeder يدوياً مرة أخرى، سيعيد ملء الرابط الفارغ.

DatabaseSeeder.php يستدعي ProductImageSeeder أيضاً، لذلك التثبيت الجديد يحصل على الصور مع نفس 50 منتجاً.

لتحديث نسخة قديمة، من مجلد backend:

```powershell
..\tools\php\php.exe artisan migrate
..\tools\php\php.exe artisan db:seed --class=ProductImageSeeder
```

تم تنفيذ Migration وImage Seeder على قاعدة البيانات الموجودة، ثم إعادة Image Seeder مرة ثانية. المقارنة قبل وبعد أكدت أن جميع حقول المنتجات السابقة و8 فئات و75 حركة بقيت كما هي. بقي عدد المنتجات 50 ومجموع المخزون 1027 وحدة.

## مصادر الصور والتحقق

تاريخ البحث: 18 أكتوبر 2026. تمت مراجعة الموديلات الخمسين الفعلية في SQLite، والبحث على الويب، وفحص صفحات الشركات. بعض الصفحات القديمة محذوفة أو تعيد التوجيه، وبعضها يحجب الطلبات؛ لذلك استعملنا صور كتالوجات عامة من بائعين عند عدم توفر صورة مباشرة من الشركة.

الصور توضيحية لنفس موديل المنتج أو عائلته. قد تختلف لغة لوحة المفاتيح AZERTY/QWERTY أو الجيل أو اللون أو رقم السعة الظاهر على صورة العائلة. بيانات SKU والثمن والكمية في التطبيق مأخوذة دائماً من قاعدة البيانات ولم تتغير.

كل رابط مستخدم أعاد HTTP 200 وContent-Type من نوع image/* وبيانات صورة غير فارغة عند GET مع Referer localhost. التفاصيل التقنية في product-image-url-checks.json. هذا تحقق من الوصول في وقت الاختبار، وليس ضماناً لبقاء المواقع متاحة دائماً. لم يتم تنزيل الصور إلى المشروع.

حقوق الصور والعلامات تبقى لأصحابها. روابط المصدر الأصلية أدناه للتوثيق والنسبة. لم نجد رخصة مفتوحة موحدة لكل هذه الصور، ولا ندّعي وجود إذن عام بإعادة نشرها؛ هذه روابط صور كتالوجات داخل مشروع تعليمي محلي. أي نشر عام يحتاج مراجعة شروط كل مصدر أو صوراً مرخصة لذلك الاستخدام.

أول رابط مختار لـCrucial SODIMM من PB Tech ورابط UGREEN Cat6 من MobileDirect أعادا 403؛ لم نستخدمهما. تم اختيار بديلين وإعادة فحصهما بنجاح. لا يوجد رابط مستخدم بقي غير متحقق منه وقت فحص HTTP. العرض في المتصفح يتم اختباره أيضاً، وقد يختلف عن فحص HTTP بسبب سياسات المواقع؛ عند الفشل تظهر الصورة المحلية تلقائياً.

| SKU | المنتج | صفحة المصدر | رابط الصورة | HTTP |
| --- | --- | --- | --- | --- |
| IT-0001 | Dell OptiPlex 7010 · i5 / 16 Go | [Source](https://www.dell.com/support/home/en-us/product-support/product/optiplex-7010-desktop/overview) | [Image](https://i.dell.com/is/image/DellContent/content/dam/ss2/product-images/dell-client-products/desktops/optiplex-desktops/optiplex-d13-tower/spi/7010-tower-plus/ng/desktop-optiplex-7010-mt-plus-black-relsize-500-ng.psd?fmt=jpg&wid=500&hei=339) | 200 |
| IT-0002 | Lenovo ThinkCentre M70q · i5 / 8 Go | [Source](https://www.shi.com/Product/43876158/Lenovo-ThinkCentre-M70q-11DU) | [Image](https://cdn.cs.1worldsync.com/c9/e6/c9e64b70-b993-4632-8a10-c2cded3987d5.jpg) | 200 |
| IT-0003 | HP ProDesk 400 G9 · i5 / 16 Go | [Source](https://www.acomputerservice.com.pe/hp/1128-pc-hp-prodesk-400-g9-sff-intel-i5-13500-250ghz-16gb-ddr4-3200mhz-1tb-ssd-9h9a2lt.html) | [Image](https://www.acomputerservice.com.pe/2391-large_default/pc-hp-prodesk-400-g9-sff-intel-i5-13500-250ghz-16gb-ddr4-3200mhz-1tb-ssd-9h9a2lt.jpg) | 200 |
| IT-0004 | Dell Latitude 5440 · 14 pouces | [Source](https://www.dell.com/support/home/en-us/product-support/product/latitude-14-5440-laptop/overview) | [Image](https://i.dell.com/is/image/DellContent/content/dam/images/products/laptops-and-2-in-1s/latitude/14-5440-non-touch/la5440nt-rgb-cnb-00000ff090-gy.psd?fmt=jpg&wid=4210&hei=2629) | 200 |
| IT-0005 | Lenovo ThinkPad E14 · Ryzen 5 | [Source](https://store.shopping.yahoo.co.jp/lenovo/21m3000xjp.html) | [Image](https://item-shopping.c.yimg.jp/i/n/lenovo_21m3000xjp) | 200 |
| IT-0006 | HP EliteBook 840 G10 · i7 | [Source](https://www.mbtech.sk/sk/hp-elitebook-840-g10-core-i5-1335u-13ghz16gb-ram512gb-ssd-pciebatterycare-266643) | [Image](https://mbtech.fra1.cdn.digitaloceanspaces.com/static/Karty/hpeb840g10_1.jpg) | 200 |
| IT-0007 | Kingston Fury 16 Go DDR4 3200 MHz | [Source](https://www.dustin.fi/product/5011251345/technology-fury-beast-muistimoduuli-16-gb-1-x-16-gb-ddr4-1600-mhz) | [Image](https://cf-images.dustin.eu/cdn-cgi/image/fit%3Dcontain%2Cformat%3Dauto%2Cquality%3D75%2Cwidth%3D828%2Cfit%3Dcontain/image/d200001001435017/kingston-technology-fury-beast-muistimoduuli-16-gb-1-x-16-gb-ddr4-1600-mhz-16gb-1600-3200mhz-ddr4-288-pin-dimm.jpg) | 200 |
| IT-0008 | Crucial 8 Go DDR4 3200 MHz | [Source](https://compx.ua/ru/modul-pamjati-ddr4-8gb-crucial-3200-mhz-ct8g4dfra32am4ff-2210141/) | [Image](https://compx.ua/image/catalog/products/227/fc24d2f3-e840-11ee-8f93-001b21ea407b.jpg) | 200 |
| IT-0009 | Corsair Vengeance 32 Go DDR4 | [Source](https://www.corsair.com/us/en/p/memory/cmk32gx4m2e3200c16/vengeancea-lpx-32gb-2-x-16gb-ddr4-dram-3200mhz-c16-memory-kit-black-cmk32gx4m2e3200c16) | [Image](https://assets.corsair.com/image/upload/c_pad,q_auto,h_1024,w_1024,f_auto/products/Memory/CMK32GX4M2E3200C16/Gallery/VENG_LPX_BLK_01.webp) | 200 |
| IT-0010 | Samsung 16 Go DDR5 4800 MHz | [Source](https://www.ceneo.pl/124124882) | [Image](https://image.ceneostatic.pl/data/products/124124882/i-pamiec-ram-samsung-16gb-ddr5-m323r2ga3bb0-cqk.jpg) | 200 |
| IT-0011 | Crucial 16 Go SODIMM DDR4 | [Source](https://www.kaufland.it/product/355716635/) | [Image](https://media.cdn.kaufland.de/product-images/1024x1024/fdb5aa21370fd1f523379673b5c78544.jpg) | 200 |
| IT-0012 | Kingston ValueRAM 8 Go DDR4 | [Source](https://www.newegg.com/kingston-technology-corp-valueram-8gb-ddr4-2133-cas-latency-cl15-desktop-memory/p/N82E16820242187) | [Image](https://c1.neweggimages.com/ProductImage/20-242-187-02.jpg) | 200 |
| IT-0013 | Samsung 990 EVO 1 To NVMe | [Source](https://www.mediamarkt.es/es/product/_disco-duro-ssd-interno-1-tb-samsung-990-evo-interno-not-available-136549934.html) | [Image](https://assets.mmsrg.com/isr/166325/c1/-/ASSET_MP_155079407/fee_786_587_png) | 200 |
| IT-0014 | Kingston NV2 500 Go NVMe | [Source](https://laptopaz.vn/o-cung-ssd-kingston-nv2-500gbnvme-pcie-4.0-x4-m.2-2280.html) | [Image](https://laptopaz.vn/media/product/2953_ktc_product_ssd_snv2s_500g_1_zm_lg.jpg) | 200 |
| IT-0015 | Crucial MX500 1 To SATA | [Source](https://24h.pchome.com.tw/prod/DCBE21-A900DEUS1) | [Image](https://cs-d.ecimg.tw/items/DCBE21A900DEUS1/000001_1644199654.jpg) | 200 |
| IT-0016 | WD Blue SN580 1 To NVMe | [Source](https://business.walmart.com/ip/WD-Blue-1TB-SN580-NVMe-Internal-SSD-WDBWMY0010BBL-WRWM/5080177169) | [Image](https://i5.walmartimages.com/seo/WD-Blue-1TB-SN580-NVMe-Internal-SSD-WDBWMY0010BBL-WRWM_28933fdc-65d2-4eef-95f4-474478cfd226.51d02b302ea9785e967da33be118bf8b.jpeg?odnBg=FFFFFF&odnHeight=640&odnWidth=640) | 200 |
| IT-0017 | Seagate Barracuda 2 To HDD | [Source](https://www.seagate.com/products/hard-drives/barracuda-hard-drive/) | [Image](https://www.seagate.com/content/dam/seagate/migrated-assets/www-content/product-content/products/hard-drives/shared/images/barracuda-3-5-drives-card-layout-products-tile-1-image.jpg) | 200 |
| IT-0018 | Samsung T7 1 To · SSD externe | [Source](https://elmir.ua/ssd-drives/ssd-drive_usb_1tb_samsung_t7_titan_gray_mu-pc1t0t_ww.html) | [Image](https://e.428.ua/img/2380126/3000/2000/ssd-nakopitel_usb_1tb_samsung_t7_titan_gray_mu-pc1t0t_ww~981~1494.jpg) | 200 |
| IT-0019 | SanDisk Ultra 128 Go USB 3.0 | [Source](https://shop.sandisk.com/products/usb-flash-drives/sandisk-ultra-usb-3-0) | [Image](https://www.sandisk.com/content/dam/store/en-us/assets/products/usb-flash-drives/ultra-usb-3-0/gallery/ultra-usb-3-0-front.png) | 200 |
| IT-0020 | WD Red Plus 4 To NAS | [Source](https://www.westerndigital.com/products/internal-drives/wd-red-plus-sata-3-5-hdd) | [Image](https://www.westerndigital.com/content/dam/store/en-us/assets/products/internal-storage/wd-red-plus-sata-3-5-hdd/gallery/wd-red-plus-sata-3-5-hdd-4tb.png.wdthumb.1280.1280.webp) | 200 |
| IT-0021 | Logitech M185 · souris sans fil | [Source](https://www.logitech.com/en-us/shop/p/m185-wireless-mouse) | [Image](https://resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/m185/gallery/2025-update-assets/m185-grey-top-angle-1.png) | 200 |
| IT-0022 | Logitech K120 · clavier AZERTY | [Source](https://www.logitech.com/en-us/shop/p/k120-usb-standard-computer) | [Image](https://resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/keyboards/k120/gallery/k120-gallery-01-new.png) | 200 |
| IT-0023 | Logitech MX Master 3S | [Source](https://www.logitech.com/en-us/shop/p/mx-master-3s) | [Image](https://resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-master-3s/2025-update/mx-master-3s-bluetooth-edition-top-view-black-new-1.png) | 200 |
| IT-0024 | Microsoft Wired Desktop 600 | [Source](https://www.incehesap.com/microsoft-desktop-600-3j2-00018-kablolu-klavye-mouse-set-fiyati-44878/) | [Image](https://www.incehesap.com/resim/urun/201912/microsoft-ms-3j2-00018-desktop-600-klavye-mouse-set-klavye-mouse-set-103481.webp) | 200 |
| IT-0025 | HP 150 · souris USB | [Source](https://notbadtech.co.nz/products/hp-150-240j6aa-usb-wired-mouse-black) | [Image](https://notbadtech.co.nz/cdn/shop/files/HP1503.jpg?v=1735712602&width=1946) | 200 |
| IT-0026 | Dell KB216 · clavier AZERTY | [Source](https://www.dell.com/en-in/shop/dell-wired-keyboard-kb216-black/apd/580-aekd/pc-accessories) | [Image](https://snpi.dell.com/snp/images/products/large/en-in~580-AEKD_v1/580-AEKD_v1.jpg) | 200 |
| IT-0027 | Logitech MK270 · ensemble sans fil | [Source](https://www.logitech.com/en-us/shop/p/mk270-wireless-keyboard-mouse) | [Image](https://resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/combos/mk270/gallery/mk270-gallery-1-new.png) | 200 |
| IT-0028 | Lenovo Essential · souris optique | [Source](https://www.trendyol.com/lenovo/4y50r20863-kablolu-mouse-1600dpi-siyah-p-281920048) | [Image](https://cdn.dsmcdn.com/ty1714/prod/QC_ENRICHMENT/20250721/00/6c9ec7c1-4ce3-3f8f-8956-505a44e2bdd5/1_org_zoom.jpg) | 200 |
| IT-0029 | TP-Link Archer AX23 · Wi-Fi 6 | [Source](https://www.tp-link.com/en/home-networking/wifi-router/archer-ax23/) | [Image](https://static.tp-link.com/1large_1627273016330o.jpg) | 200 |
| IT-0030 | TP-Link TL-SG108 · switch 8 ports | [Source](https://www.tp-link.com/us/business-networking/unmanaged-switch/tl-sg108/) | [Image](https://static.tp-link.com/upload/image-line/TL-SG108_UN_5.0_01_large_20231024111419x.jpg) | 200 |
| IT-0031 | Ubiquiti UniFi U6 Lite | [Source](https://techspecs.ui.com/unifi/wifi/u6-lite) | [Image](https://cdn.ecomm.ui.com/products/259686b4-ae75-411c-90bc-e4040e38ca56/3dac99a9-6352-44f3-ac8b-ade89c707831.png) | 200 |
| IT-0032 | D-Link DGS-1016D · switch 16 ports | [Source](https://www.dlink.com/en/products/dgs-1016d-16-port-gigabit-unmanaged-desktop-switch) | [Image](https://www.dlink.com/en/-/media/product-pages/dgs/1016d/dgs1016dfront.png) | 200 |
| IT-0033 | TP-Link UE300 · adaptateur Ethernet | [Source](https://www.tp-link.com/us/home-networking/usb-converter/ue300/) | [Image](https://static.tp-link.com/1_large_1557038845349f.jpg) | 200 |
| IT-0034 | Intel AX210 · carte Wi-Fi 6E | [Source](https://www.bol.com/nl/nl/p/wifi-6e-m-2-wireless-card-met-bluetooth-5-2-voor-laptop-mu-mimo-ondersteunt-windows-10-11-linux/9300000249995045/) | [Image](https://media.s-bol.com/q181VD1PVWD3/j2m1j9P/550x549.jpg) | 200 |
| IT-0035 | Netgear GS305 · switch 5 ports | [Source](https://www.netgear.com/business/wired/switches/unmanaged/gs305/) | [Image](https://assets.netgear.com/transform/pdp-mobile/b37661dd-661c-4883-a45b-f8f2048dab9d/GS305_productcarousel_hero_image) | 200 |
| IT-0036 | Dell P2422H · 24 pouces FHD | [Source](https://www.dell.com/support/home/en-us/product-support/product/dell-p2422h-monitor/overview) | [Image](https://i.dell.com/is/image/DellContent//content/dam/ss2/product-images/dell-client-products/peripherals/monitors/p-series/p2422h/spi/ng/monitor-p2422h-relsize-500-ng.psd?qlt=95&fit=constrain,1&hei=344&wid=500&fmt=jpg) | 200 |
| IT-0037 | HP M24f · 24 pouces IPS | [Source](https://www.hpshop.co.za/products/hp-m24f-23-8-inch-1920-x-1080p-fhd-16-9-75hz-5ms-amd-freesync-ips-monitor-2d9k0as-321195) | [Image](https://www.hpshop.co.za/cdn/shop/files/7e79a6b1-0bde-4b87-b112-010a4331de5e-1.jpg?v=1741354230&width=1214) | 200 |
| IT-0038 | Samsung S27C310 · 27 pouces | [Source](https://comfy.ua/monitor-samsung-s27c310-ls27c310eaixci.html) | [Image](https://cdn.comfy.ua/media/catalog/product/cache/5/image/600x/9df78eab33525d08d6e5fb8d27136e95/m/y/my-essential-s3-27s31c-ls27c310eaexxs-535603850.jpg) | 200 |
| IT-0039 | LG 27UP650 · 27 pouces 4K | [Source](https://www.lg.com/jp/monitors/4k-5k-monitors/27up650-w/) | [Image](https://www.lg.com/content/dam/channel/wcms/jp/images/monitor/27up650-w_ajl_ejjp_jp_c/27UP650-W_AJL_EJJP_JP_C-450x450.jpg) | 200 |
| IT-0040 | Lenovo ThinkVision T24i-30 | [Source](https://www.sklep-lenovo.pl/monitor-lenovo-thinkvision-t24i-30-63cfmatxeu.html) | [Image](https://www.sklep-lenovo.pl/media/products/0cc6ff86fb6b6d1e77bc57d92a9c9875/images/thumbnail/large_T24i-30.png?lm=1737737690) | 200 |
| IT-0041 | HP LaserJet Pro M404dn | [Source](https://www.hp.com/us-en/shop/pdp/hp-laserjet-pro-m404dn) | [Image](https://hp.widen.net/content/ikce5f2ejw/webp/ikce5f2ejw.png?w=573&h=430&dpi=72&color=ffffff00) | 200 |
| IT-0042 | Brother HL-L2350DW · laser | [Source](https://www.brother-usa.com/products/hll2350dw) | [Image](https://assets.brother.com/transform/9e10532c-7486-43cf-831d-0f47e1270ad2/20689_Refresh_Spinner_updateHL-L2350dw-jpg?io=transform:fit,width:680) | 200 |
| IT-0043 | Canon PIXMA G3410 · multifonction | [Source](https://www.canon-europe.com/printers/pixma-g3410/) | [Image](https://cdn.media.amplience.net/i/canon/pixma_g3410-as-fra_4c70ad9f96f948a8b32187fb69d362d9?$prod-gallery-1by1-jpg$) | 200 |
| IT-0044 | HP 59A · toner noir | [Source](https://business.currys.co.uk/catalogue/printers/ink-toner/toner-inks/hp-59a-black-original-laserjet-toner-cartridge-cf259a/P275130P) | [Image](https://cdn.cs.1worldsync.com/94/98/9498118b-9cb9-4e05-9f41-8603b2785e37.jpg) | 200 |
| IT-0045 | Epson 103 · kit encre 4 couleurs | [Source](https://www.acihellas.gr/epst00s64a) | [Image](https://www.acihellas.gr/content/images/thumbs/0059438_epson-103-4-inkjet-printer-cartridges-multipack-yellow-cyan-magenta-black-c13t00s64a-epst00s64a-epst00s64a_0.jpeg) | 200 |
| IT-0046 | UGREEN USB-C · hub 6-en-1 | [Source](https://24h.pchome.com.tw/prod/DCACBY-A900G4MEU) | [Image](https://img.pchome.com.tw/cs/items/DCACBYA900G4MEU/000001_1718788104.jpg) | 200 |
| IT-0047 | Belkin HDMI 2.1 · 2 mètres | [Source](https://24h.pchome.com.tw/prod/DCACI0-A900IU6N4) | [Image](https://img.pchome.com.tw/cs/items/DCACI0A900IU6N4/000001_1747722838.jpg) | 200 |
| IT-0048 | UGREEN Cat6 RJ45 · 3 mètres | [Source](https://www.mundonerdslz.com.br/cabo-de-rede-cat-6-com-3-metros-uutp-ugreen) | [Image](https://cdn.awsli.com.br/2500x2500/2521/2521575/produto/304297894/cabo-de-rede-cat-6-com-3-metros--uutp---ugreen-eq0c1ozksr.png) | 200 |
| IT-0049 | Anker USB-C 65 W · chargeur | [Source](https://www.anker.com/products/a2663) | [Image](https://cdn.shopify.com/s/files/1/0493/9834/9974/products/A2663111-Anker_715_Charger_Nano_II_65W.png?v=1767756360) | 200 |
| IT-0050 | StarTech DisplayPort vers HDMI | [Source](https://portal.shi.com/product/17611827/StarTech.com-DisplayPort-to-HDMI-Adapter) | [Image](https://cdn.cs.1worldsync.com/82/48/82482cec-1eb3-421a-b6b0-463f44668e91.jpg) | 200 |

## الملفات المعدلة

- Backend: Product.php، ProductRequest.php، ProductController.php، DatabaseSeeder.php، Migration الجديدة وProductImageSeeder.php.
- Frontend: Products.jsx، ProductDetail.jsx، UI.jsx، style.css، مع ProductCard.jsx وpublic/product-placeholder.svg جديدين.
- Tests: ProductImagesTest.php جديد، وفحص صور التثبيت الجديد في InventoryTest.php.
- Documentation: هذا الملف، README.md، DESIGN.md، وملفات JSON الخاصة بنتائج التحقق.

لم نغير StockService أو routes أو Auth أو dependencies.

## نتائج الاختبارات الفعلية

- PHPUnit: نجح 41 tests و401 assertions، بما فيها Auth وroles وStock IN/OUT وعدم كفاية المخزون وimmutability وsearch وpagination وimage URL validation وSeeder idempotence.
- npm run build: نجح باستخدام Vite. لا توجد dependencies جديدة.
- HTTP smoke: نجح 11 checks، تشمل CSRF وSession وCORS ورفض تعديلات Employee بـ403.
- Migration نجحت على SQLite الموجودة. Image Seeder تْشغّل مرتين. لم نحذف أو ننشئ منتجات إضافية.
- كل الصور الخمسين تحمّلت فعلياً في browser، وnaturalWidth وnaturalHeight أكبر من صفر. القياسات في product-image-browser-checks.json، ونتائج GET في product-image-url-checks.json.
- Browser: تحقّقنا من 4 أعمدة عند 1280px و2 عند 800px و1 عند 390px، بدون overflow أفقي للصفحة.
- Browser: بحث Logitech أعاد 4 كروت، والتنقل إلى الصفحة 2 اشتغل. Employee رأى التفاصيل بدون زر Modifier.
- Browser: اختبرنا Preview برابط فارغ ورابط مكسور. ثم حفظنا رابطاً مكسوراً مؤقتاً، فظهرت fallback في التفاصيل والقائمة، وبعدها أعدنا رابط Anker الأصلي وحفظناه بنجاح.
- المقارنة الأخيرة في product-images-preservation-check.json تؤكد بقاء 50 منتجاً و8 فئات و75 حركة و1027 وحدة، وجميع business fields كما كانت. استثنينا updated_at من المقارنة الأخيرة لأنه يسجل وقت اختبار حفظ صورة IT-0049؛ بعد Migration وSeeder مباشرة كانت كل timestamps السابقة أيضاً كما هي.
- المراجعة البصرية المستقلة: Ready، ولا توجد تصحيحات مطلوبة. تنبيه detector الوحيد يخص Arial، وهو الخط المعتمد أصلاً في هذا المشروع البسيط.

| مجال المراجعة | النتيجة |
| --- | --- |
| نفس التصميم البسيط | Pass |
| معلومات المنتج والأزرار | Pass |
| Responsive layout | Pass |
| الصور وPreview وFallback | Pass |
| الصلاحيات وواجهة المخزون والحركات | Pass |
| النتيجة العامة | Ready; no corrections required |
