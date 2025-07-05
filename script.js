// Menunggu hingga seluruh konten halaman (HTML) selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    // Meminta lokasi pengguna menggunakan Geolocation API dari browser
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(posisiBerhasil, posisiGagal);
    } else {
        alert("Geolocation tidak didukung oleh browser ini.");
    }
});

// Fungsi yang dijalankan jika berhasil mendapatkan lokasi
function posisiBerhasil(posisi) {
    const latitude = posisi.coords.latitude;
    const longitude = posisi.coords.longitude;
    
    // Panggil fungsi untuk mengambil jadwal sholat dengan koordinat yang didapat
    fetchJadwalSholat(latitude, longitude);
}

// Fungsi yang dijalankan jika gagal mendapatkan lokasi
function posisiGagal() {
    alert("Gagal mendapatkan lokasi Anda. Pastikan Anda mengizinkan akses lokasi.");
    // Jika gagal, tampilkan pesan error di halaman
    document.getElementById('lokasi').innerText = 'Tidak dapat mengakses lokasi.';
    document.getElementById('jadwal-sholat').innerText = 'Izinkan akses lokasi untuk melihat jadwal sholat.';
}

// Fungsi untuk mengambil data jadwal sholat dari API
async function fetchJadwalSholat(lat, lon) {
    try {
        // Menggunakan API dari Aladhan. Metode 20 adalah metode Kemenag RI.
        const respons = await fetch(`https://api.aladhan.com/v1/timings?latitude=${lat}&longitude=${lon}&method=20`);
        const data = await respons.json();

        if (data.code === 200) {
            // Jika data berhasil didapat, tampilkan
            tampilkanJadwal(data.data);
        } else {
            // Jika ada masalah dengan API
            throw new Error('Gagal mengambil data jadwal sholat.');
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('jadwal-sholat').innerText = 'Terjadi kesalahan saat mengambil data.';
    }
}

// Fungsi untuk menampilkan jadwal sholat di halaman HTML
function tampilkanJadwal(data) {
    // Menampilkan lokasi (zona waktu) dari data API
    const lokasiEl = document.getElementById('lokasi');
    lokasiEl.innerText = `Lokasi: ${data.meta.timezone}`;

    // Memilih elemen utama untuk jadwal sholat
    const jadwalContainer = document.getElementById('jadwal-sholat');
    // Mengosongkan konten "Memuat..." sebelumnya
    jadwalContainer.innerHTML = '';

    const waktuPenting = {
        "Subuh": data.timings.Fajr,
        "Dzuhur": data.timings.Dhuhr,
        "Ashar": data.timings.Asr,
        "Maghrib": data.timings.Maghrib,
        "Isya": data.timings.Isha,
    };

    // Membuat elemen HTML untuk setiap waktu sholat
    for (const nama in waktuPenting) {
        const div = document.createElement('div');
        div.classList.add('waktu-sholat');
        
        const spanNama = document.createElement('span');
        spanNama.innerText = nama;
        
        const spanWaktu = document.createElement('span');
        spanWaktu.innerText = waktuPenting[nama];

        div.appendChild(spanNama);
        div.appendChild(spanWaktu);

        jadwalContainer.appendChild(div);
    }
}