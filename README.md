# JSON User Creator Documentation
# مستندات افزونه JSON User Creator

[English](#english) | [فارسی](#persian)

<a name="english"></a>
## English

### Overview
JSON User Creator allows you to bulk create WordPress users from a JSON file. It supports mapping JSON fields to user attributes and handles Persian/Arabic text conversion.

> **⚠️ IMPORTANT**: This plugin was developed for personal use and may contain bugs. Please backup your database before using it.

### How to Use
1. Upload a JSON file containing user data
2. Map JSON fields to WordPress user fields (Username, Email, First Name, Last Name)
3. Preview the mapped data in real-time
4. Optionally enable post author updates
5. Click "Create Users" to process the import

### Features
- Automatic Persian/Arabic to English conversion for usernames
- Automatic email generation if not provided
- Real-time preview of converted data
- Post author update capability
- Duplicate username handling
- Bulk update of post authors using post_id field

### JSON Format
Your JSON file should be an array of objects containing user data. Example:

```json
[
    {
        "username": "john-doe",
        "email": "john@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "post_id": [12, 121, 332, 45]  // Posts that will be assigned to this user
    }
]
```

The `post_id` field allows you to specify which posts should be authored by each user. When provided, the plugin will automatically update the author of these posts to the newly created user.

### Contributing
This plugin is intentionally kept simple and straightforward. However, there's always room for improvement! If you:
- Find any bugs or issues
- Have ideas for new features
- Want to improve the documentation
- See opportunities for code optimization

Please feel free to:
1. Open an issue to report bugs or suggest features
2. Submit a pull request with your improvements
3. Fork the project and build upon it

Your contributions, no matter how small, are greatly appreciated and will help make this plugin better for everyone!

---

<a name="persian"></a>
## فارسی

### نمای کلی
JSON User Creator به شما امکان می‌دهد تا کاربران وردپرس را به صورت گروهی از طریق فایل JSON ایجاد کنید. این افزونه از نگاشت فیلدهای JSON به ویژگی‌های کاربر پشتیبانی می‌کند و تبدیل متن فارسی/عربی را مدیریت می‌کند.

> **⚠️ مهم**: این افزونه برای استفاده شخصی نوشته شده است و ممکن است اشکالات فراوانی داشته باشد. در نتیجه پیش از استفاده از دیتابیس خود بک‌آپ بگیرید.

### نحوه استفاده
۱. آپلود فایل JSON حاوی اطلاعات کاربران
۲. نگاشت فیلدهای JSON به فیلدهای کاربری وردپرس (نام کاربری، ایمیل، نام، نام خانوادگی)
۳. پیش‌نمایش داده‌های نگاشت شده به صورت بلادرنگ
۴. فعال‌سازی اختیاری به‌روزرسانی نویسنده پست‌ها
۵. کلیک روی "ایجاد کاربران" برای پردازش ورود اطلاعات

### ویژگی‌ها
- تبدیل خودکار متن فارسی/عربی به انگلیسی برای نام‌های کاربری
- تولید خودکار ایمیل در صورت عدم ارائه
- پیش‌نمایش بلادرنگ داده‌های تبدیل شده
- قابلیت به‌روزرسانی نویسنده پست‌ها
- مدیریت نام‌های کاربری تکراری
- به‌روزرسانی گروهی نویسنده پست‌ها با استفاده از فیلد post_id

فیلد `post_id` به شما امکان می‌دهد مشخص کنید که کدام پست‌ها باید توسط هر کاربر نوشته شوند. هنگامی که این فیلد ارائه می‌شود، افزونه به طور خودکار نویسنده این پست‌ها را به کاربر جدید تغییر می‌دهد.

### قالب JSON
فایل JSON شما باید آرایه‌ای از اشیاء حاوی اطلاعات کاربر باشد. مثال:

```json
[
    {
        "username": "ali-ahmadi",
        "email": "ali.ahmadi@example.com",
        "first_name": "علی",
        "last_name": "احمدی",
        "post_id": [12, 121, 332, 45]
    }
]
```

### مشارکت
این افزونه عمداً ساده و سرراست نگه داشته شده است. با این حال، همیشه جای پیشرفت وجود دارد! اگر شما:
- باگ یا مشکلی پیدا کردید
- ایده‌ای برای ویژگی‌های جدید دارید
- می‌خواهید مستندات را بهبود دهید
- فرصت‌هایی برای بهینه‌سازی کد می‌بینید

لطفاً:
۱. یک issue باز کنید تا باگ‌ها را گزارش یا ویژگی‌های جدید را پیشنهاد دهید
۲. یک pull request با بهبودهای خود ارسال کنید
۳. پروژه را fork کنید و روی آن توسعه دهید

مشارکت‌های شما، هر چقدر هم کوچک، بسیار ارزشمند هستند و به بهتر شدن این افزونه برای همه کمک خواهند کرد!

---

### Contact | تماس
Telegram: [@chalist](https://t.me/chalist)  
Email: [chalist1@gmail.com](mailto:chalist1@gmail.com)
