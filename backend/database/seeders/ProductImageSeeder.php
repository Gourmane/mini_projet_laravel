<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        // Direct image URLs researched on 2026-10-18. Sources: PRODUCT_IMAGES.md.
        // Only empty images are filled; administrator choices are preserved.
        $images = [
            // Dell OptiPlex 7010 · i5 / 16 Go
            'IT-0001' => 'https://i.dell.com/is/image/DellContent/content/dam/ss2/product-images/dell-client-products/desktops/optiplex-desktops/optiplex-d13-tower/spi/7010-tower-plus/ng/desktop-optiplex-7010-mt-plus-black-relsize-500-ng.psd?fmt=jpg&wid=500&hei=339',
            // Lenovo ThinkCentre M70q · i5 / 8 Go
            'IT-0002' => 'https://cdn.cs.1worldsync.com/c9/e6/c9e64b70-b993-4632-8a10-c2cded3987d5.jpg',
            // HP ProDesk 400 G9 · i5 / 16 Go
            'IT-0003' => 'https://www.acomputerservice.com.pe/2391-large_default/pc-hp-prodesk-400-g9-sff-intel-i5-13500-250ghz-16gb-ddr4-3200mhz-1tb-ssd-9h9a2lt.jpg',
            // Dell Latitude 5440 · 14 pouces
            'IT-0004' => 'https://i.dell.com/is/image/DellContent/content/dam/images/products/laptops-and-2-in-1s/latitude/14-5440-non-touch/la5440nt-rgb-cnb-00000ff090-gy.psd?fmt=jpg&wid=4210&hei=2629',
            // Lenovo ThinkPad E14 · Ryzen 5
            'IT-0005' => 'https://item-shopping.c.yimg.jp/i/n/lenovo_21m3000xjp',
            // HP EliteBook 840 G10 · i7
            'IT-0006' => 'https://mbtech.fra1.cdn.digitaloceanspaces.com/static/Karty/hpeb840g10_1.jpg',
            // Kingston Fury 16 Go DDR4 3200 MHz
            'IT-0007' => 'https://cf-images.dustin.eu/cdn-cgi/image/fit%3Dcontain%2Cformat%3Dauto%2Cquality%3D75%2Cwidth%3D828%2Cfit%3Dcontain/image/d200001001435017/kingston-technology-fury-beast-muistimoduuli-16-gb-1-x-16-gb-ddr4-1600-mhz-16gb-1600-3200mhz-ddr4-288-pin-dimm.jpg',
            // Crucial 8 Go DDR4 3200 MHz
            'IT-0008' => 'https://compx.ua/image/catalog/products/227/fc24d2f3-e840-11ee-8f93-001b21ea407b.jpg',
            // Corsair Vengeance 32 Go DDR4
            'IT-0009' => 'https://assets.corsair.com/image/upload/c_pad,q_auto,h_1024,w_1024,f_auto/products/Memory/CMK32GX4M2E3200C16/Gallery/VENG_LPX_BLK_01.webp',
            // Samsung 16 Go DDR5 4800 MHz
            'IT-0010' => 'https://image.ceneostatic.pl/data/products/124124882/i-pamiec-ram-samsung-16gb-ddr5-m323r2ga3bb0-cqk.jpg',
            // Crucial 16 Go SODIMM DDR4
            'IT-0011' => 'https://media.cdn.kaufland.de/product-images/1024x1024/fdb5aa21370fd1f523379673b5c78544.jpg',
            // Kingston ValueRAM 8 Go DDR4
            'IT-0012' => 'https://c1.neweggimages.com/ProductImage/20-242-187-02.jpg',
            // Samsung 990 EVO 1 To NVMe
            'IT-0013' => 'https://assets.mmsrg.com/isr/166325/c1/-/ASSET_MP_155079407/fee_786_587_png',
            // Kingston NV2 500 Go NVMe
            'IT-0014' => 'https://laptopaz.vn/media/product/2953_ktc_product_ssd_snv2s_500g_1_zm_lg.jpg',
            // Crucial MX500 1 To SATA
            'IT-0015' => 'https://cs-d.ecimg.tw/items/DCBE21A900DEUS1/000001_1644199654.jpg',
            // WD Blue SN580 1 To NVMe
            'IT-0016' => 'https://i5.walmartimages.com/seo/WD-Blue-1TB-SN580-NVMe-Internal-SSD-WDBWMY0010BBL-WRWM_28933fdc-65d2-4eef-95f4-474478cfd226.51d02b302ea9785e967da33be118bf8b.jpeg?odnBg=FFFFFF&odnHeight=640&odnWidth=640',
            // Seagate Barracuda 2 To HDD
            'IT-0017' => 'https://www.seagate.com/content/dam/seagate/migrated-assets/www-content/product-content/products/hard-drives/shared/images/barracuda-3-5-drives-card-layout-products-tile-1-image.jpg',
            // Samsung T7 1 To · SSD externe
            'IT-0018' => 'https://e.428.ua/img/2380126/3000/2000/ssd-nakopitel_usb_1tb_samsung_t7_titan_gray_mu-pc1t0t_ww~981~1494.jpg',
            // SanDisk Ultra 128 Go USB 3.0
            'IT-0019' => 'https://www.sandisk.com/content/dam/store/en-us/assets/products/usb-flash-drives/ultra-usb-3-0/gallery/ultra-usb-3-0-front.png',
            // WD Red Plus 4 To NAS
            'IT-0020' => 'https://www.westerndigital.com/content/dam/store/en-us/assets/products/internal-storage/wd-red-plus-sata-3-5-hdd/gallery/wd-red-plus-sata-3-5-hdd-4tb.png.wdthumb.1280.1280.webp',
            // Logitech M185 · souris sans fil
            'IT-0021' => 'https://resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/m185/gallery/2025-update-assets/m185-grey-top-angle-1.png',
            // Logitech K120 · clavier AZERTY
            'IT-0022' => 'https://resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/keyboards/k120/gallery/k120-gallery-01-new.png',
            // Logitech MX Master 3S
            'IT-0023' => 'https://resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/mice/mx-master-3s/2025-update/mx-master-3s-bluetooth-edition-top-view-black-new-1.png',
            // Microsoft Wired Desktop 600
            'IT-0024' => 'https://www.incehesap.com/resim/urun/201912/microsoft-ms-3j2-00018-desktop-600-klavye-mouse-set-klavye-mouse-set-103481.webp',
            // HP 150 · souris USB
            'IT-0025' => 'https://notbadtech.co.nz/cdn/shop/files/HP1503.jpg?v=1735712602&width=1946',
            // Dell KB216 · clavier AZERTY
            'IT-0026' => 'https://snpi.dell.com/snp/images/products/large/en-in~580-AEKD_v1/580-AEKD_v1.jpg',
            // Logitech MK270 · ensemble sans fil
            'IT-0027' => 'https://resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/logitech/en/products/combos/mk270/gallery/mk270-gallery-1-new.png',
            // Lenovo Essential · souris optique
            'IT-0028' => 'https://cdn.dsmcdn.com/ty1714/prod/QC_ENRICHMENT/20250721/00/6c9ec7c1-4ce3-3f8f-8956-505a44e2bdd5/1_org_zoom.jpg',
            // TP-Link Archer AX23 · Wi-Fi 6
            'IT-0029' => 'https://static.tp-link.com/1large_1627273016330o.jpg',
            // TP-Link TL-SG108 · switch 8 ports
            'IT-0030' => 'https://static.tp-link.com/upload/image-line/TL-SG108_UN_5.0_01_large_20231024111419x.jpg',
            // Ubiquiti UniFi U6 Lite
            'IT-0031' => 'https://cdn.ecomm.ui.com/products/259686b4-ae75-411c-90bc-e4040e38ca56/3dac99a9-6352-44f3-ac8b-ade89c707831.png',
            // D-Link DGS-1016D · switch 16 ports
            'IT-0032' => 'https://www.dlink.com/en/-/media/product-pages/dgs/1016d/dgs1016dfront.png',
            // TP-Link UE300 · adaptateur Ethernet
            'IT-0033' => 'https://static.tp-link.com/1_large_1557038845349f.jpg',
            // Intel AX210 · carte Wi-Fi 6E
            'IT-0034' => 'https://media.s-bol.com/q181VD1PVWD3/j2m1j9P/550x549.jpg',
            // Netgear GS305 · switch 5 ports
            'IT-0035' => 'https://assets.netgear.com/transform/pdp-mobile/b37661dd-661c-4883-a45b-f8f2048dab9d/GS305_productcarousel_hero_image',
            // Dell P2422H · 24 pouces FHD
            'IT-0036' => 'https://i.dell.com/is/image/DellContent//content/dam/ss2/product-images/dell-client-products/peripherals/monitors/p-series/p2422h/spi/ng/monitor-p2422h-relsize-500-ng.psd?qlt=95&fit=constrain,1&hei=344&wid=500&fmt=jpg',
            // HP M24f · 24 pouces IPS
            'IT-0037' => 'https://www.hpshop.co.za/cdn/shop/files/7e79a6b1-0bde-4b87-b112-010a4331de5e-1.jpg?v=1741354230&width=1214',
            // Samsung S27C310 · 27 pouces
            'IT-0038' => 'https://cdn.comfy.ua/media/catalog/product/cache/5/image/600x/9df78eab33525d08d6e5fb8d27136e95/m/y/my-essential-s3-27s31c-ls27c310eaexxs-535603850.jpg',
            // LG 27UP650 · 27 pouces 4K
            'IT-0039' => 'https://www.lg.com/content/dam/channel/wcms/jp/images/monitor/27up650-w_ajl_ejjp_jp_c/27UP650-W_AJL_EJJP_JP_C-450x450.jpg',
            // Lenovo ThinkVision T24i-30
            'IT-0040' => 'https://www.sklep-lenovo.pl/media/products/0cc6ff86fb6b6d1e77bc57d92a9c9875/images/thumbnail/large_T24i-30.png?lm=1737737690',
            // HP LaserJet Pro M404dn
            'IT-0041' => 'https://hp.widen.net/content/ikce5f2ejw/webp/ikce5f2ejw.png?w=573&h=430&dpi=72&color=ffffff00',
            // Brother HL-L2350DW · laser
            'IT-0042' => 'https://assets.brother.com/transform/9e10532c-7486-43cf-831d-0f47e1270ad2/20689_Refresh_Spinner_updateHL-L2350dw-jpg?io=transform:fit,width:680',
            // Canon PIXMA G3410 · multifonction
            'IT-0043' => 'https://cdn.media.amplience.net/i/canon/pixma_g3410-as-fra_4c70ad9f96f948a8b32187fb69d362d9?$prod-gallery-1by1-jpg$',
            // HP 59A · toner noir
            'IT-0044' => 'https://cdn.cs.1worldsync.com/94/98/9498118b-9cb9-4e05-9f41-8603b2785e37.jpg',
            // Epson 103 · kit encre 4 couleurs
            'IT-0045' => 'https://www.acihellas.gr/content/images/thumbs/0059438_epson-103-4-inkjet-printer-cartridges-multipack-yellow-cyan-magenta-black-c13t00s64a-epst00s64a-epst00s64a_0.jpeg',
            // UGREEN USB-C · hub 6-en-1
            'IT-0046' => 'https://img.pchome.com.tw/cs/items/DCACBYA900G4MEU/000001_1718788104.jpg',
            // Belkin HDMI 2.1 · 2 mètres
            'IT-0047' => 'https://img.pchome.com.tw/cs/items/DCACI0A900IU6N4/000001_1747722838.jpg',
            // UGREEN Cat6 RJ45 · 3 mètres
            'IT-0048' => 'https://cdn.awsli.com.br/2500x2500/2521/2521575/produto/304297894/cabo-de-rede-cat-6-com-3-metros--uutp---ugreen-eq0c1ozksr.png',
            // Anker USB-C 65 W · chargeur
            'IT-0049' => 'https://cdn.shopify.com/s/files/1/0493/9834/9974/products/A2663111-Anker_715_Charger_Nano_II_65W.png?v=1767756360',
            // StarTech DisplayPort vers HDMI
            'IT-0050' => 'https://cdn.cs.1worldsync.com/82/48/82482cec-1eb3-421a-b6b0-463f44668e91.jpg',
        ];

        foreach ($images as $sku => $imageUrl) {
            if ($imageUrl === null) continue;

            DB::table('products')->where('sku', $sku)
                ->where(function ($query) {
                    $query->whereNull('image_url')->orWhere('image_url', '');
                })
                ->update(['image_url' => $imageUrl]);
        }
    }
}
