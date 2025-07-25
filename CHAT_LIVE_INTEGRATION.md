# توثيق دمج الدردشة الحية (Live Chat Integration)

## 1. إعداد Laravel Broadcasting (Pusher)

### أ. إعدادات .env
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=2027799
PUSHER_APP_KEY=07bde3e92cfef0fbf03a
PUSHER_APP_SECRET=a4e081c28a580d2d0b
PUSHER_APP_CLUSTER=eu
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
```

### ب. إعداد config/broadcasting.php
```php
'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true,
        ],
    ],
    // ...
],
```

### ج. إعداد قنوات البث (routes/channels.php)
```php
Broadcast::channel('consultation.{consultationId}', function ($user, $consultationId) {
    // تحقق من صلاحية المستخدم
    return true;
});
Broadcast::channel('typing.consultation.{consultationId}', function ($user, $consultationId) {
    return true;
});
```

### د. الأحداث (Events)
- **NewConsultationMessage**: يبث الرسائل الجديدة.
- **TypingIndicator**: يبث مؤشر الكتابة.

---

## 2. ربط Next.js مع Laravel Echo & Pusher

### أ. التثبيت
```bash
npm install laravel-echo pusher-js
```

### ب. التهيئة
```js
// echo.js
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

export const echo = new Echo({
  broadcaster: "pusher",
  key: "07bde3e92cfef0fbf03a",
  cluster: "eu",
  forceTLS: true,
  encrypted: true,
  authEndpoint: "http://localhost:8000/broadcasting/auth",
  auth: {
    headers: {
      Authorization: "Bearer {USER_TOKEN}",
      "X-API-KEY": "{API_KEY}"
    }
  }
});
```

### ج. الاستماع للرسائل الجديدة
```js
import { echo } from './echo';

echo.private(`consultation.${consultationId}`)
  .listen('NewConsultationMessage', (e) => {
    // e.message يحوي الرسالة الجديدة
    console.log('رسالة جديدة:', e.message);
  });
```

### د. الاستماع لمؤشر الكتابة
```js
echo.private(`typing.consultation.${consultationId}`)
  .listen('TypingIndicator', (e) => {
    // e.user_id هو المستخدم الذي يكتب الآن
    console.log('يكتب الآن:', e.user_id);
  });
```

---

## 3. ربط Flutter مع Pusher

### أ. التثبيت
```yaml
dependencies:
  pusher_channels_flutter: ^2.2.0
```

### ب. التهيئة والاشتراك
```dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

final pusher = PusherChannelsFlutter.getInstance();

await pusher.init(
  apiKey: "07bde3e92cfef0fbf03a",
  cluster: "eu",
  onConnectionStateChange: (x) => print(x.currentState),
  onError: (x) => print(x.message),
  onEvent: (event) {
    print("حدث جديد: ${event.eventName} - ${event.data}");
  },
  authEndpoint: "http://localhost:8000/broadcasting/auth",
  headers: {
    "Authorization": "Bearer {USER_TOKEN}",
    "X-API-KEY": "{API_KEY}"
  }
);

await pusher.subscribe(channelName: "private-consultation.{consultationId}");
await pusher.bind(
  channelName: "private-consultation.{consultationId}",
  eventName: "NewConsultationMessage",
  callback: (event) {
    print("رسالة جديدة: ${event.data}");
  }
);

await pusher.subscribe(channelName: "private-typing.consultation.{consultationId}");
await pusher.bind(
  channelName: "private-typing.consultation.{consultationId}",
  eventName: "TypingIndicator",
  callback: (event) {
    print("يكتب الآن: ${event.data}");
  }
);
```

---

## 4. إرسال مؤشر الكتابة من الواجهة

- عند بدء الكتابة في صندوق الرسائل:
  - أرسل طلب POST:
    ```http
    POST /api/consultations/{consultation_id}/typing
    Authorization: Bearer {USER_TOKEN}
    X-API-KEY: {API_KEY}
    ```

---

## 5. ملاحظات مهمة
- Laravel يضيف تلقائياً بادئة `private-` للقنوات الخاصة.
- يجب أن يكون المستخدم موثّق (Sanctum) ليستطيع الاشتراك في القنوات الخاصة.
- إذا واجهت مشاكل في التوثيق، تأكد من إرسال التوكن وAPI KEY في headers.
- يمكنك اختبار البث من لوحة Pusher مباشرة أو من Laravel عبر `event(new NewConsultationMessage(...))`.

---

## 6. مصادر وروابط مفيدة
- [Laravel Broadcasting Docs](https://laravel.com/docs/broadcasting)
- [Pusher Channels Docs](https://pusher.com/docs/channels)
- [laravel-echo](https://github.com/laravel/echo)
- [pusher_channels_flutter](https://pub.dev/packages/pusher_channels_flutter) 