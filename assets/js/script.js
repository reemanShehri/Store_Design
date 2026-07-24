document.addEventListener('DOMContentLoaded', function() {

    // =============================================
    // 1. إعداد الكانفاس
    // =============================================
    const canvas = new fabric.Canvas('tshirt-canvas', {
        width: 500,
        height: 600,
        backgroundColor: '#fafafa'
    });

    // =============================================
    // 2. المتغيرات العامة
    // =============================================
    let currentProductId = null;
    let currentColorImage = null;
    let currentSize = 'L';
    let currentDesignId = null;

    // كائنات مضافة (سنحتفظ بها في مصفوفات)
    let uploadedImageObject = null;   // الصورة المرفوعة من المستخدم
    let designImageObject = null;     // التصميم الجاهز المختار
    let userImageFile = null;         // ملف الصورة المرفوعة

    // =============================================
    // 3. دالة تحميل صورة القميص (الخلفية)
    //    هذه الدالة تُغير الخلفية فقط، ولا تمس الكائنات الأخرى
    // =============================================



    // تست
    // function loadTshirt(imageUrl) {
    //     if (!imageUrl) {
    //         console.warn('⚠️ لا توجد صورة للقميص');
    //         return;
    //     }

    //     // مسح الخلفية القديمة (دون التأثير على الكائنات الأخرى)
    //     canvas.setBackgroundImage(null, canvas.renderAll.bind(canvas));

    //     fabric.Image.fromURL(imageUrl, function(img) {
    //         if (!img) {
    //             console.error('❌ فشل تحميل الصورة:', imageUrl);
    //             return;
    //         }
    //         canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
    //             scaleX: canvas.width / img.width,
    //             scaleY: canvas.height / img.height,
    //             originX: 'center',
    //             originY: 'center'
    //         });
    //         canvas.renderAll();
    //         console.log('✅ تم تحميل الخلفية:', imageUrl);
    //     }, {
    //         crossOrigin: 'anonymous',
    //         onError: function(err) {
    //             console.error('❌ خطأ في التحميل:', err);
    //         }
    //     });
    // }



    function clearAllOverlays() {
    if (uploadedImageObject) {
        canvas.remove(uploadedImageObject);
        uploadedImageObject = null;
        userImageFile = null;
    }
    if (designImageObject) {
        canvas.remove(designImageObject);
        designImageObject = null;
    }
    // إزالة أي كائنات أخرى عالقة (باستثناء الخلفية)
    const objects = canvas.getObjects();
    objects.forEach(obj => {
        if (obj !== canvas.backgroundImage) {
            canvas.remove(obj);
        }
    });
    canvas.renderAll();
}
    // =============================================
    // 4. دالة لإضافة كائن (صورة أو تصميم) فوق الخلفية
    // =============================================
    function addOverlayImage(url, type = 'user') {
        // type: 'user' للصورة المرفوعة، 'design' للتصميم الجاهز

        fabric.Image.fromURL(url, function(img) {
            if (!img) return;

            const maxW = 250, maxH = 320;
            let scale = Math.min(maxW / img.width, maxH / img.height, 1);
            img.scale(scale);
            img.set({
                originX: 'center',
                originY: 'center',
                left: canvas.width / 2,
                top: canvas.height / 2 - 20,
                selectable: true,
                hasControls: true,
                hasBorders: true,
                borderColor: '#2980b9',
                cornerColor: '#2980b9'
            });

            // حسب النوع، نخزن في المتغير المناسب
            if (type === 'user') {
                // إذا كانت هناك صورة مرفوعة سابقة، نزيلها
                if (uploadedImageObject) {
                    canvas.remove(uploadedImageObject);
                }
                uploadedImageObject = img;
            } else if (type === 'design') {
                // إذا كان هناك تصميم سابق، نزيله
                if (designImageObject) {
                    canvas.remove(designImageObject);
                }
                designImageObject = img;
            }

            canvas.add(img);
            canvas.setActiveObject(img);
            canvas.renderAll();
            console.log(`✅ تم إضافة ${type === 'user' ? 'صورة مرفوعة' : 'تصميم جاهز'}`);
        }, { crossOrigin: 'anonymous' });
    }



    // دالة لإضافة صورة مع الحفاظ على الخلفية
function addOverlayImageSafe(url, type = 'user') {
    // حفظ مرجع الخلفية الحالية
    const currentBg = canvas.backgroundImage;

    fabric.Image.fromURL(url, function(img) {
        if (!img) return;
        const maxW = 250, maxH = 320;
        let scale = Math.min(maxW / img.width, maxH / img.height, 1);
        img.scale(scale);
        img.set({
            originX: 'center',
            originY: 'center',
            left: canvas.width / 2,
            top: canvas.height / 2 - 20,
            selectable: true,
            hasControls: true,
            hasBorders: true,
            borderColor: '#2980b9',
            cornerColor: '#2980b9'
        });

        if (type === 'user') {
            if (uploadedImageObject) canvas.remove(uploadedImageObject);
            uploadedImageObject = img;
        } else if (type === 'design') {
            if (designImageObject) canvas.remove(designImageObject);
            designImageObject = img;
        }

        canvas.add(img);
        canvas.bringToFront(img);
        canvas.renderAll();
        console.log(`✅ تم إضافة ${type === 'user' ? 'صورة' : 'تصميم'} مع بقاء الخلفية`);
    }, { crossOrigin: 'anonymous' });
}



    // =============================================
    // 5. دالة تحميل المنتج (تعرض ألوانه وتصاميمه)
    // =============================================
    function loadProduct(productId) {

addOverlayImageSafe();


        // clearAllOverlays();
        const data = productData[productId];
        if (!data) {
            console.warn('⚠️ المنتج غير موجود:', productId);
            return;
        }

        currentProductId = productId;

        // --- عرض الألوان كبطاقات ---
        const colorContainer = document.getElementById('colorOptions');
        colorContainer.innerHTML = '';
        if (data.colors && data.colors.length > 0) {
            data.colors.forEach((color, index) => {
                const card = document.createElement('div');
                card.className = `color-card ${index === 0 ? 'active' : ''}`;
                card.dataset.id = color.id;
                card.dataset.color = color.color_name;
                card.dataset.image = color.image_path;
                card.draggable = true;

                card.innerHTML = `
                    <img src="${color.image_path}" alt="${color.color_name}">
                    <span>${color.color_name}</span>
                `;
                colorContainer.appendChild(card);

                // حدث النقر على اللون → يغير الخلفية فقط
                card.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const img = this.dataset.image;
                    if (!img) return;
                    loadTshirt(img);
                    document.querySelectorAll('.color-card').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    currentColorImage = img;
                });

                // حدث السحب (لإفلاته على الكانفاس)
                card.addEventListener('dragstart', function(e) {
                    e.dataTransfer.setData('text/plain', this.dataset.image);
                    e.dataTransfer.effectAllowed = 'copy';
                });
            });

            // تحميل أول لون تلقائياً
            const first = data.colors[0];
            currentColorImage = first.image_path;
            loadTshirt(first.image_path);
        } else {
            colorContainer.innerHTML = '<p style="color:#e74c3c;">⚠️ لا توجد ألوان لهذا المنتج</p>';
        }

        // --- عرض التصاميم المقترحة ---
        const designsContainer = document.getElementById('designsGrid');
        designsContainer.innerHTML = '';
        if (data.designs && data.designs.length > 0) {
            data.designs.forEach(design => {
                const div = document.createElement('div');
                div.className = 'design-item';
                div.dataset.id = design.id;
                div.dataset.image = design.image_path;
                div.innerHTML = `
                    <img src="${design.image_path}" alt="${design.name}">
                    <span>${design.name}</span>
                `;
                designsContainer.appendChild(div);
            });
        } else {
            designsContainer.innerHTML = '<p class="no-designs">لا توجد تصاميم مقترحة</p>';
        }

        // --- تحديث البطاقات النشطة ---
        document.querySelectorAll('.product-card').forEach(card => {
            card.classList.toggle('active', parseInt(card.dataset.productId) === productId);
        });

        // إزالة الكائنات المضافة السابقة (صور مرفوعة، تصاميم) عند تغيير المنتج
        if (uploadedImageObject) {
            canvas.remove(uploadedImageObject);
            uploadedImageObject = null;
            userImageFile = null;
        }
        if (designImageObject) {
            canvas.remove(designImageObject);
            designImageObject = null;
        }
        currentDesignId = null;
        document.querySelectorAll('.design-item').forEach(d => d.classList.remove('active'));
        canvas.renderAll();
    }

    // =============================================
    // 6. أحداث اختيار المنتج (من البطاقات)
    // =============================================
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            const pid = parseInt(this.dataset.productId);
            if (pid === currentProductId) return;
            loadProduct(pid);
        });
    });

    // =============================================
    // 7. أحداث المقاس
    // =============================================
    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSize = this.dataset.size;
        });
    });

    // =============================================
    // 8. رفع الصورة عبر الزر (تضاف كصورة مرفوعة)
    // =============================================
    // document.getElementById('uploadImage').addEventListener('change', function(e) {
    //     const file = e.target.files[0];
    //     if (!file) return;
    //     const reader = new FileReader();
    //     reader.onload = function(ev) {
    //         userImageFile = file;
    //         addOverlayImageSafe(ev.target.result, 'user');
    //     };
    //     reader.readAsDataURL(file);
    //     this.value = '';
    // });




    document.getElementById('uploadImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) {
        alert('⚠️ يرجى اختيار صورة');
        return;
    }

    if (!currentProductId) {
        alert('⚠️ يرجى اختيار منتج أولاً');
        this.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('product_id', currentProductId);
    formData.append('design_image', file);
    formData.append('design_name', file.name.split('.')[0] || 'تصميم جديد');

    const btn = this.previousElementSibling;
    const originalText = btn.textContent;
    btn.textContent = '⏳ جاري الرفع...';

    fetch('upload_design.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const newDesign = {
                id: data.design_id,
                name: data.design_name,
                image_path: data.image_path
            };

            if (productData[currentProductId]) {
                if (!productData[currentProductId].designs) {
                    productData[currentProductId].designs = [];
                }
                productData[currentProductId].designs.push(newDesign);
            }

            const designsContainer = document.getElementById('designsGrid');
            const div = document.createElement('div');
            div.className = 'design-item';
            div.dataset.id = newDesign.id;
            div.dataset.image = newDesign.image_path;
            div.innerHTML = `
                <img src="${newDesign.image_path}" alt="${newDesign.name}">
                <span>${newDesign.name}</span>
            `;
            designsContainer.appendChild(div);

            alert('✅ تم رفع التصميم بنجاح!');
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ فشل الاتصال بالخادم');
    })
    .finally(() => {
        btn.textContent = originalText;
        this.value = '';
    });
});

    // =============================================
    // 9. إزالة الصورة المرفوعة
    // =============================================
    document.getElementById('removeImage').addEventListener('click', function() {
        if (uploadedImageObject) {
            canvas.remove(uploadedImageObject);
            uploadedImageObject = null;
            userImageFile = null;
            canvas.renderAll();
        }
    });

    // =============================================
    // 10. مسح الكل (يزيل جميع الكائنات المضافة)
    // =============================================
    document.getElementById('clearAll').addEventListener('click', function() {
        if (uploadedImageObject) {
            canvas.remove(uploadedImageObject);
            uploadedImageObject = null;
            userImageFile = null;
        }
        if (designImageObject) {
            canvas.remove(designImageObject);
            designImageObject = null;
        }
        document.querySelectorAll('.design-item').forEach(d => d.classList.remove('active'));
        currentDesignId = null;
        if (currentColorImage) loadTshirt(currentColorImage);
        canvas.renderAll();
    });

    // =============================================
    // 11. اختيار تصميم جاهز (يضاف كتصميم)
    // =============================================
    document.addEventListener('click', function(e) {
        const item = e.target.closest('.design-item');
        if (item) {
            const image = item.dataset.image;
            const id = parseInt(item.dataset.id);
            document.querySelectorAll('.design-item').forEach(d => d.classList.remove('active'));
            item.classList.add('active');
            currentDesignId = id;
            addOverlayImage(image, 'design');
        }
    });

    // =============================================
    // 12. السحب والإفلات (على منطقة التصميم)
    // =============================================
    const designArea = document.getElementById('designArea');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(ev => {
        document.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); }, false);
        designArea.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); }, false);
    });

    designArea.addEventListener('dragenter', function() { this.classList.add('dragover'); });
    designArea.addEventListener('dragleave', function() { this.classList.remove('dragover'); });

    designArea.addEventListener('drop', function(e) {
        this.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('⚠️ يرجى إسقاط ملف صورة فقط');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(ev) {
            userImageFile = file;
            addOverlayImage(ev.target.result, 'user');
        };
        reader.readAsDataURL(file);
    });

    // =============================================
    // 13. حدث الإفلات على الكانفاس (لتغيير الخلفية بسرعة)
    // =============================================
    canvas.wrapperEl.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });


    // =============================================
// حدث الإفلات على الكانفاس (لإضافة صورة قابلة للتحريك والتحجيم)
// =============================================
canvas.wrapperEl.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
});

canvas.wrapperEl.addEventListener('drop', function(e) {
    e.preventDefault();
    const raw = e.dataTransfer.getData('text/plain');

    // 1. إذا كانت البيانات صورة (من بطاقة لون أو منتج)
    if (raw && (raw.startsWith('http') || raw.startsWith('/') || raw.startsWith('uploads') || raw.startsWith('data:image'))) {
        // ✅ إضافة الصورة ككائن علوي (قابل للسحب والتحجيم) بدلاً من تغيير الخلفية
        addOverlayImage(raw, 'user');

        // تحديث البطاقة النشطة (للتمييز فقط)
        document.querySelectorAll('.color-card, .product-card').forEach(c => {
            c.classList.toggle('active', c.dataset.image === raw);
        });
        return;
    }

    // 2. إذا كان الملف من سطح المكتب (ملف صورة)
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            userImageFile = file;
            addOverlayImage(ev.target.result, 'user');
        };
        reader.readAsDataURL(file);
    }
});

// تتت

    // canvas.wrapperEl.addEventListener('drop', function(e) {
    //     e.preventDefault();
    //     const raw = e.dataTransfer.getData('text/plain');
    //     // إذا كانت البيانات صورة (من بطاقة لون أو منتج)
    //     if (raw && (raw.startsWith('http') || raw.startsWith('/') || raw.startsWith('uploads') || raw.startsWith('data:image'))) {
    //         loadTshirt(raw);
    //         document.querySelectorAll('.color-card, .product-card').forEach(c => {
    //             c.classList.toggle('active', c.dataset.image === raw);
    //         });
    //         currentColorImage = raw;
    //         return;
    //     }
    //     // إذا كان ملفاً من سطح المكتب
    //     const file = e.dataTransfer.files[0];
    //     if (file && file.type.startsWith('image/')) {
    //         const reader = new FileReader();
    //         reader.onload = function(ev) {
    //             userImageFile = file;
    //             addOverlayImage(ev.target.result, 'user');
    //         };
    //         reader.readAsDataURL(file);
    //     }
    // });

    // =============================================
    // 14. تصدير PNG
    // =============================================
    document.getElementById('exportBtn').addEventListener('click', function() {
        const dataURL = canvas.toDataURL({ format: 'png', quality: 1 });
        const link = document.createElement('a');
        link.download = `تيشيرت-${Date.now()}.png`;
        link.href = dataURL;
        link.click();
    });

    // =============================================
    // 15. الطلب عبر واتساب
    // =============================================
    document.getElementById('orderBtn').addEventListener('click', function() {
        if (!uploadedImageObject && !currentDesignId) {
            alert('⚠️ يرجى رفع صورة أو اختيار تصميم جاهز');
            return;
        }
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '⏳ جاري الطلب...';

        const formData = new FormData();
        formData.append('product_id', currentProductId || 0);
        formData.append('color', currentColorImage ? currentColorImage.split('/').pop().replace(/\.[^.]+$/, '') : 'white');
        formData.append('size', currentSize);
        if (currentDesignId) formData.append('design_id', currentDesignId);
        if (userImageFile) formData.append('user_image', userImageFile);

        fetch('place_order.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const msg = `🛍 طلب تصميم  جديد\n\n📋 رقم الطلب: ${data.order_number}\n📏 المقاس: ${currentSize}\n🎨 اللون: ${data.color_name || ''}\n🖼 ${currentDesignId ? 'تصميم جاهز' : 'صورة مرفوعة'}\n\n📸 يرجى إرسال صورة تأكيدية.`;
                window.open(`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(msg)}`, '_blank');
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(err => { alert('❌ فشل الاتصال'); console.error(err); })
        .finally(() => { btn.disabled = false; btn.innerHTML = '📲 طلب عبر واتساب'; });
    });

    // =============================================
    // 16. زر إزالة الخلفية
    // =============================================
   // =============================================
// زر إزالة الخلفية – يفتح الموقع مباشرة
// =============================================
document.getElementById('removeBgBtn').addEventListener('click', function() {
    window.open('https://www.remove.bg/upload', '_blank');
});

    // =============================================
    // 17. تحميل أول منتج عند بدء التشغيل
    // =============================================
    const firstProduct = document.querySelector('.product-card');
    if (firstProduct) {
        const pid = parseInt(firstProduct.dataset.productId);
        loadProduct(pid);
    } else {
        console.warn('⚠️ لا يوجد منتجات لعرضها');
    }

    console.log('✅ صمم منتجك  ستور جاهز للعمل!');
});


// دالة لإعادة ترتيب الطبقات
function reorderLayers() {
    // نضع الخلفية في الخلف (إذا كانت موجودة)
    const objects = canvas.getObjects();
    objects.forEach(obj => {
        if (obj.isBackground) { // نضع علامة للخلفية
            canvas.sendToBack(obj);
        }
    });
    // نضع الصورة المرفوعة والتصميم في الأمام
    if (uploadedImageObject) {
        canvas.bringToFront(uploadedImageObject);
    }
    if (designImageObject) {
        canvas.bringToFront(designImageObject);
    }
    canvas.renderAll();
}

function loadTshirt(imageUrl) {
    // ...
    fabric.Image.fromURL(imageUrl, function(img) {
        // ...
        img.isBackground = true; // علامة للخلفية
        canvas.setBackgroundImage(img, function() {
            canvas.renderAll();
            reorderLayers(); // إعادة ترتيب بعد التحميل
        }, {
            scaleX: canvas.width / img.width,
            scaleY: canvas.height / img.height,
            originX: 'center',
            originY: 'center'
        });
    });
}


function addOverlayImage(url, type = 'user') {
    fabric.Image.fromURL(url, function(img) {
        // ...
        // حسب النوع، نخزن في المتغير المناسب دون إزالة الآخر
        if (type === 'user') {
            if (uploadedImageObject) canvas.remove(uploadedImageObject);
            uploadedImageObject = img;
        } else if (type === 'design') {
            if (designImageObject) canvas.remove(designImageObject);
            designImageObject = img;
        }
        canvas.add(img);
        canvas.bringToFront(img); // اجعلها في الأمام
        canvas.renderAll();
        reorderLayers();
    });
}


function refreshCanvas() {
    canvas.clear();
    // إعادة تحميل الخلفية
    if (currentColorImage) loadTshirt(currentColorImage);
    // إعادة إضافة الصورة المرفوعة
    if (uploadedImageObject) {
        // لكننا فقدنا الكائن، لذا نعيد إنشائه من الملف
        if (userImageFile) {
            const reader = new FileReader();
            reader.onload = function(e) {
                addOverlayImage(e.target.result, 'user');
            };
            reader.readAsDataURL(userImageFile);
        }
    }
    // إعادة إضافة التصميم
    if (currentDesignId) {
        // نبحث عن التصميم في productData
        // ...
    }
}
