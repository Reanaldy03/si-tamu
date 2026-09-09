# SI-TAMU — Digital Guestbook Development Kit

Paket ini berisi seluruh dokumen spesifikasi rekayasa perangkat lunak, sistem pelacakan progres, dan rancangan prompt mutakhir untuk membangun aplikasi **SI-TAMU** (Sistem Informasi Buku Tamu Digital Berbasis Web) menggunakan Laravel, MySQL, Blade, Tailwind CSS, dan Alpine.js.

---

## Isi Berkas dalam Paket Ini

1. **`PRD.md`** — *Product Requirements Document*  
   Mendefinisikan latar belakang masalah dari 4 studi jurnal, scope MVP, user persona, alur interaksi, acceptance criteria (FR-001 s.d. FR-017), KPI sukses, dan batasan non-goals.

2. **`Architecture.md`** — *Software Architecture Document*  
   Mendefinisikan arsitektur *Modular Monolith* Laravel, pembagian layer (Presentation, Application, Domain, Infrastructure), struktur folder, aliran data atomik, strategi keamanan, deployment topology, dan integrasi masa depan.

3. **`Design.md`** — *Product Design & UI/UX Specification*  
   Mendefinisikan alur antarmuka pengguna, information architecture, design tokens (warna institusional, typography sans-serif, spacing 4px), spesifikasi formulir publik 1-halaman, layout back-office, aturan status visual, dan aksesibilitas.

4. **`Schema.md`** — *Database Schema Specification*  
   Mendefinisikan skema presisi MySQL 8.x (InnoDB, utf8mb4, UTC timestamp), DDL lengkap untuk 11 tabel inti + 5 tabel framework, referential action (RESTRICT/SET NULL/CASCADE), indeks komposit, dan aturan denormalisasi snapshot.

5. **`Rules.md`** — *Engineering Rules & Contribution Guardrails*  
   Mendefinisikan aturan koding PSR-12, strict types, action-based use case, zero logic di Blade/Controller, larangan "AI slop" visual, sanitasi formula spreadsheet, dan protokol keamanan data pribadi.

6. **`PROGRESS.md`** — *Project Ledger & State Tracker*  
   File pelacak status dan checklist tugas. File ini wajib ditaruh di root workspace proyek dan diupdate oleh AI di setiap akhir sprint agar AI tidak lupa konteks (*no context drift*).

7. **`PROMPTS.md`** — *Master AI Prompt Engine*  
   Kumpulan prompt mutakhir siap pakai, mencakup Master System Prompt, Re-anchoring Prompt, Sprint Roadmap 1 s.d. 9, dan standar Handoff.

---

## Cara Menggunakan Paket Ini dengan AI Coding Assistant

### Skenario A: Menggunakan Cursor / Windsurf / Claude Code
1. Ekstrak seluruh isi berkas ini ke dalam folder root proyek Laravel Anda:
   ```text
   my-sitamu-project/
   ├── PRD.md
   ├── Architecture.md
   ├── Design.md
   ├── Schema.md
   ├── Rules.md
   ├── PROGRESS.md
   ├── PROMPTS.md
   └── README.md
   ```
2. Buka file `.cursorrules` atau System Prompt di IDE Anda, lalu tempelkan **Master System Prompt** dari `PROMPTS.md` Bagian 2.
3. Kirimkan prompt **Sprint 1** untuk mulai membuat migrasi dan struktur awal.
4. Setiap kali AI menyelesaikan satu sprint, periksa checklist di `PROGRESS.md`, lalu lanjutkan ke sprint berikutnya.

### Skenario B: Menggunakan ChatGPT / Claude Web / Gemini
1. Unggah berkas `PRD.md`, `Architecture.md`, `Design.md`, `Schema.md`, dan `Rules.md`.
2. Tempelkan **Master System Prompt** sebagai instruksi awal bersama file `PROGRESS.md`.
3. Jalankan eksekusi sprint demi sprint sesuai panduan di `PROMPTS.md`.
4. Jika obrolan terputus atau membuka jendela chat baru, gunakan prompt **Re-Anchoring** dari `PROMPTS.md` Bagian 3.
