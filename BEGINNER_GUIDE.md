# دليل بسيط لفهم IT Stock Manager

## 1. فكرة المشروع

هذا التطبيق يساعدنا على تنظيم matériel informatique. نضيف Produit وCatégorie، ثم نسجل كل Entrée وSortie. نستطيع معرفة الكمية الحالية ومراجعة Historique.

- **Frontend** هو الواجهة التي تظهر في navigateur. نستعمل React لعرض الصفحات والتفاعل مع المستخدم.
- **Backend** هو Laravel. يستقبل الطلبات، يتحقق من البيانات ومن صلاحيات المستخدم، ثم يتعامل مع قاعدة البيانات.
- **SQLite** هي قاعدة بيانات موجودة في ملف واحد: `backend/database/database.sqlite`.

التواصل يكون هكذا:

`React → Axios → Route Laravel → Controller → Model → SQLite → JSON → React`

البيانات تأتي من API حقيقية، ولا توجد قائمة produits ثابتة في React.

## 2. الملفات والمجلدات المهمة

كل المسارات التالية تبدأ من مجلد `it-stock-manager/`.

| المسار | دوره |
| --- | --- |
| `frontend/src/main.jsx` | يشغل React وBrowserRouter وAuthProvider |
| `frontend/src/App.jsx` | يحدد أي Page تظهر لكل URL ويحمي الصفحات |
| `frontend/src/pages/` | يحتوي Login وDashboard وProducts وProductDetail وCategories وMovementNew وHistory |
| `frontend/src/components/Navbar.jsx` | روابط التنقل وزر logout |
| `frontend/src/components/UI.jsx` | عناصر صغيرة مشتركة: Loading وErrorState وPagination وStockBadge |
| `frontend/src/api/api.js` | إعداد Axios وcookies ورسائل الخطأ |
| `frontend/src/context/AuthContext.jsx` | المستخدم الحالي وlogin وlogout واسترجاع Session |
| `frontend/src/styles/style.css` | كل CSS، بما فيه عرض الهاتف |
| `backend/routes/api.php` | عناوين API المحمية |
| `backend/routes/web.php` | login وlogout مع Session وCSRF |
| `backend/app/Http/Controllers/` | استقبال Request وإرجاع Response |
| `backend/app/Http/Requests/` | Validation: ما البيانات المسموح بها؟ |
| `backend/app/Http/Middleware/AdminOnly.php` | يمنع employé من تعديل catalogue |
| `backend/app/Models/` | Product وCategory وStockMovement وUser وعلاقات Eloquent |
| `backend/app/Services/StockService.php` | تعديل الكمية وتسجيل Historique في Transaction واحدة |
| `backend/database/migrations/` | إنشاء الجداول وForeign Keys وحماية SQLite |
| `backend/database/seeders/DatabaseSeeder.php` | حسابا démonstration و50 Produit الأصلية |
| `backend/config/` | إعداد SQLite وSession وSanctum وCORS |
| `backend/tests/Feature/InventoryTest.php` | اختبارات API والصلاحيات والمخزون في SQLite منفصلة في الذاكرة |
| `tools/` | PHP وComposer لتشغيل المشروع على هذا الجهاز؛ ليس كود التطبيق |

لماذا بقيت ملفات Requests؟ حتى لا تختلط قواعد Validation مع استعلامات Controller. هذه طريقة Laravel عادية، وليست طبقة إضافية مثل Repository.

## 3. مثال كامل: فتح صفحة Products

1. المستخدم يفتح `/products`.
2. `frontend/src/App.jsx` يعرض `frontend/src/pages/Products.jsx`.
3. داخل `useEffect` ننفذ `api.get("/api/products?" + query)`.
4. `frontend/src/api/api.js` يرسل الطلب إلى Laravel مع Session cookie.
5. `backend/routes/api.php` يمرر الطلب عبر `auth:sanctum` ثم إلى `ProductController@index`.
6. `backend/app/Http/Controllers/ProductController.php` يتحقق من filters ويستعمل `Product::with('category')`.
7. Model في `backend/app/Models/Product.php` يستعمل Eloquent لقراءة SQLite. العلاقة `category()` تربط Produit بـCatégorie.
8. `paginate()` يرجع JSON فيه `data` و`current_page` و`last_page` و`total`.
9. React يخزن Response في `products` بواسطة `setProducts`.
10. `products.data.map(...)` يعرض سطرًا لكل Produit في Table.

عندما نغير filter أو page، يتغير URL ويعمل useEffect من جديد. عند إغلاق الصفحة أو تغيير filter نلغي الطلب القديم بواسطة AbortController حتى لا يعرض Response قديمة.

## 4. كيف ننظم State؟

- `useState`: للـform وloading وerror وmessage وبيانات الصفحة.
- `props`: لإرسال بيانات قريبة. مثال: Products يمرر `data` و`onPage` إلى Pagination.
- `AuthContext`: فقط للمستخدم وSession لأن Navbar والصفحات وRoutes تحتاجها.
- `useAuth()`: اختصار صغير لـuseContext، وليس نظامًا لجلب البيانات.
- لا توجد Redux أو Redux Toolkit أو Zustand أو Reducers أو Context لجميع Produits.
- كل Page تستعمل Axios مباشرة. لا يوجد custom hook مثل useApi.
- نستعمل `async/await` و`try/catch/finally`: نحاول تحميل البيانات، نعرض الخطأ إن وقع، ثم ننهي loading.

تكرار بضعة أسطر للتحميل مقصود: تستطيع قراءة Page وفهمها دون الانتقال بين عدة abstractions.

## 5. ماذا يحدث عند تسجيل OUT؟

افترض أن الكمية الحالية 10 ونريد إخراج 3.

1. نفتح `frontend/src/pages/MovementNew.jsx` ونختار Produit وSortie وquantity = 3.
2. React يعرض preview: 10 → 7. بعد المراجعة نضغط Confirmer le mouvement.
3. Axios يرسل POST إلى `/api/movements` مع product_id وtype وquantity وnote.
4. `backend/routes/api.php` يتحقق من Session بواسطة Sanctum.
5. `backend/app/Http/Requests/MovementRequest.php` يرفض quantity السالبة أو الصفر أو الكسر ويتحقق من Produit.
6. `MovementController@store` يأخذ User من Session، وليس من Request يرسلها المستخدم.
7. `backend/app/Services/StockService.php` يبدأ `DB::transaction()`.
8. أول SQL يعدل المخزون بشرط أن Produit نشط وquantity الحالية تكفي. التحقق والتعديل في SQL واحدة يمنع مشكلة طلبين في نفس الوقت.
9. إذا لم ينجح التعديل، يرجع Laravel خطأ 422 ولا يسجل Mouvement.
10. إذا نجح، تصبح الكمية 7. نسجل StockMovement فيه quantity = 3 وquantity_before = 10 وquantity_after = 7 وuser_id.
11. إذا فشل تسجيل Historique، Transaction تلغي تعديل الكمية أيضًا.
12. عند النجاح ترجع Response 201. React يعرض رسالة نجاح والكمية الجديدة.

Preview يساعد المستخدم، لكن Backend هو الذي يقرر حسب المخزون الحقيقي وقت الحفظ. عند SQLite lock مؤقت، يحاول StockService ثلاث مرات كحد أقصى، ثم يرجع 503 إذا بقيت القاعدة مشغولة.

لا نعدل quantity من Product form. لا نحذف Produit نهائيًا: يمكن جعله inactif مع الحفاظ على المخزون والتاريخ. تصحيح Mouvement يكون بتسجيل Mouvement جديد؛ لا نمسح التاريخ.

## 6. Authentication وRoles

في Login، يطلب React أولًا `/sanctum/csrf-cookie`، ثم يرسل `/login`. Laravel يتحقق من password المحفوظة بشكل hash ويغير Session ID.

Axios يرسل cookies مع الطلبات. لا نخزن password أو Token في localStorage. عند logout نلغي Session. عند 401 أو419 نمسح User من Context ونرجع إلى Login.

Admin يستطيع إضافة وتعديل Produits وCatégories وتغيير Produit actif. Employé يستطيع القراءة وتسجيل IN/OUT. إخفاء الأزرار في React يساعد الواجهة، لكن الحماية الحقيقية موجودة في Middleware وRequests على Laravel.

## 7. ترتيب القراءة والتعلم

ابدأ بهذه الملفات:

1. `frontend/src/pages/Products.jsx`: useState وuseEffect وAxios وmap.
2. `backend/routes/api.php`: ربط URL بـController.
3. `backend/app/Http/Controllers/ProductController.php`: filters وEloquent وpagination.
4. `backend/app/Models/Product.php`: علاقة category وحساب stock_status.
5. `frontend/src/pages/ProductDetail.jsx` و`backend/app/Http/Requests/ProductRequest.php`: form وValidation.
6. `frontend/src/pages/MovementNew.jsx` ثم `backend/app/Services/StockService.php`.
7. `frontend/src/context/AuthContext.jsx` ثم AuthController وAdminOnly.
8. Migration وSeeder ثم Tests.

## 8. كيف تعيد بناء المشروع بنفسك؟

1. ابنِ Products ثابتة بـReact وHTML table وuseState.
2. أنشئ Laravel API بسيطة ترجع JSON من Route وController.
3. اربط React بـLaravel بواسطة Axios واعرض loading وerror.
4. أنشئ SQLite tables بواسطة migrations وModel relationships.
5. أضف Products create/read/update وValidation؛ quantity تبدأ من صفر.
6. أضف Categories واستعمل category_id في form.
7. أضف IN/OUT وStockService وTransaction وHistorique.
8. أضف login/logout بواسطة Session وSanctum وCSRF.
9. أضف Admin وEmployé وحماية Laravel وRoutes React.
10. أضف Dashboard بأربع إحصائيات من API.
11. اختبر: OUT أكبر من المتاح، duplicate SKU، Produit inactif، employé يعدل catalogue، rollback وlogin/logout.

لا تبدأ بكل الصفحات مرة واحدة. بعد كل خطوة، شغل التطبيق وفسر لنفسك: من أين جاءت البيانات؟ من تحقق منها؟ أين خُزنت؟

## 9. التشغيل والاختبارات

من مجلد المشروع افتح Terminal للـBackend وشغل `./start-backend.ps1`، وآخر للـFrontend وشغل `./start-frontend.ps1`. افتح `http://localhost:5173`.

من داخل `backend/`:
`../tools/php/php.exe vendor/phpunit/phpunit/phpunit`

من داخل `frontend/`:
`npm run build`

Tests تستعمل SQLite في الذاكرة، لذلك لا تمسح بيانات démonstration. لا تستعمل `migrate:fresh` على قاعدة بياناتك الحالية؛ هذا الأمر يحذفها.

الأجزاء التي تحتاج دراسة أكثر هي Session/CSRF وTransaction وSQL triggers. بقاؤها ضروري لحماية الحسابات والمخزون؛ تعلمها تدريجيًا بعد فهم CRUD.

