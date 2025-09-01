# Tutor LMS Midtrans Snap Gateway

Plugin WordPress untuk mengintegrasikan gateway pembayaran Midtrans Snap dengan Tutor LMS.

## Deskripsi

Plugin ini memungkinkan pengguna Tutor LMS untuk melakukan pembayaran menggunakan berbagai metode pembayaran yang didukung oleh Midtrans, termasuk:

- Kartu Kredit/Debit
- GoPay
- ShopeePay
- Virtual Account (BCA, BNI, BRI, Permata)
- E-Channel

## Fitur

- ✅ Integrasi penuh dengan Tutor LMS
- ✅ Dukungan environment Sandbox dan Production
- ✅ Pilihan checkout style (Redirect/Popup)
- ✅ Konfigurasi payment channels yang fleksibel
- ✅ Verifikasi signature untuk keamanan
- ✅ Handling IPN (Instant Payment Notification)
- ✅ Email notifikasi otomatis
- ✅ Logging untuk debugging
- ✅ PSR-4 autoloading
- ✅ Standar keamanan WordPress

## Persyaratan Sistem

- WordPress 5.0+
- PHP 7.4+
- Tutor LMS plugin
- Akun Midtrans (Sandbox dan/atau Production)

## Instalasi

1. Upload folder `tutor-midtrans-gateway` ke direktori `/wp-content/plugins/`
2. Aktifkan plugin melalui menu 'Plugins' di WordPress admin
3. Pastikan Tutor LMS sudah terinstall dan aktif
4. Konfigurasi gateway di **Tutor LMS → Settings → Payments → Midtrans Snap**

## Konfigurasi

### 1. Enable Gateway
Aktifkan atau nonaktifkan gateway pembayaran Midtrans Snap.

### 2. Environment
- **Sandbox**: Untuk testing dan development
- **Production**: Untuk transaksi live

### 3. Checkout Style
- **Redirect**: User akan diarahkan ke halaman Midtrans
- **Popup**: Pembayaran akan ditampilkan dalam popup

### 4. Payment Channels
Pilih metode pembayaran yang ingin diaktifkan:
- `credit_card` - Kartu Kredit/Debit
- `gopay` - GoPay
- `shopeepay` - ShopeePay
- `bca_va` - BCA Virtual Account
- `bni_va` - BNI Virtual Account
- `bri_va` - BRI Virtual Account
- `permata_va` - Permata Virtual Account
- `echannel` - E-Channel

### 5. API Keys

#### Sandbox (Testing)
- **Server Key**: Dapatkan dari Midtrans Dashboard Sandbox
- **Client Key**: Dapatkan dari Midtrans Dashboard Sandbox

#### Production (Live)
- **Server Key**: Dapatkan dari Midtrans Dashboard Production
- **Client Key**: Dapatkan dari Midtrans Dashboard Production

## Cara Kerja

### 1. Proses Pembayaran
1. User mengklik tombol "Pay with Midtrans"
2. Plugin membuat Snap token via AJAX
3. User diarahkan ke Midtrans atau popup dibuka
4. User menyelesaikan pembayaran
5. Midtrans mengirim notifikasi ke plugin
6. Plugin memproses status pembayaran
7. User di-enroll ke course jika pembayaran berhasil

### 2. Order ID Format
Plugin menggunakan format order ID: `tutor_order_{course_id}_{timestamp}`

Contoh: `tutor_order_123_1640995200`

### 3. Verifikasi Keamanan
- Nonce verification untuk AJAX requests
- Signature verification untuk IPN
- Capability checks untuk admin settings
- Data sanitization dan escaping

## Hooks dan Filters

### Actions
- `tutor_midtrans_payment_success` - Dipanggil saat pembayaran berhasil
- `tutor_midtrans_payment_failed` - Dipanggil saat pembayaran gagal
- `tutor_midtrans_payment_pending` - Dipanggil saat pembayaran pending

### Filters
- `tutor_midtrans_payment_params` - Filter parameter pembayaran
- `tutor_midtrans_enrollment_data` - Filter data enrollment

## Troubleshooting

### 1. Gateway tidak muncul
- Pastikan plugin Tutor LMS aktif
- Periksa apakah gateway di-enable di settings
- Periksa error log WordPress

### 2. Pembayaran gagal
- Periksa konfigurasi API keys
- Pastikan environment (sandbox/production) sesuai
- Periksa error log Midtrans

### 3. IPN tidak berfungsi
- Pastikan URL callback dikonfigurasi di Midtrans Dashboard
- Periksa apakah server dapat diakses dari internet
- Periksa error log WordPress

### 4. User tidak ter-enroll
- Periksa apakah course ID valid
- Pastikan user sudah login
- Periksa error log untuk detail error

## Logging

Plugin menggunakan `error_log()` untuk logging. Log dapat ditemukan di:
- WordPress debug log (jika WP_DEBUG_LOG aktif)
- Server error log
- Plugin-specific log (jika dikonfigurasi)

## Keamanan

- Semua input di-sanitize
- Output di-escape
- Nonce verification untuk AJAX
- Capability checks untuk admin
- Signature verification untuk IPN
- Rate limiting consideration

## Support

Untuk dukungan teknis:
1. Periksa dokumentasi ini
2. Periksa error log
3. Hubungi developer plugin
4. Konsultasi dengan Midtrans support

## Changelog

### Version 1.0.0
- Initial release
- Integrasi dengan Tutor LMS
- Support Midtrans Snap API
- Multiple payment channels
- IPN handling
- Security features

## License

GPL v2 atau yang lebih baru

## Credits

- Developed for Tutor LMS
- Midtrans Snap API integration
- WordPress coding standards compliance
