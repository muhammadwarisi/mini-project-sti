<script setup>
import AppIcon from './AppIcon.vue'
</script>

<template>
  <div class="auth">
    <aside class="auth__hero">
      <div class="auth__brand">
        <span class="brand-mark">
          <AppIcon name="check" :size="20" />
        </span>
        <span class="brand-name">TugasKita</span>
      </div>

      <div class="auth__hero-body">
        <p class="eyebrow eyebrow--light">Manajemen tugas kelas</p>
        <h2>
          Belajar lebih fokus,<br />
          tugas tetap <span>terkendali.</span>
        </h2>
        <p class="auth__lead">
          Satu tempat untuk guru membuat tugas dan siswa memantau progresnya, tanpa
          catatan yang tercecer.
        </p>

        <ul class="auth__features">
          <li>
            <span class="feature-icon"><AppIcon name="users" :size="19" /></span>
            <div>
              <strong>Dua peran, satu alur</strong>
              <span>Guru mengelola, siswa menuntaskan.</span>
            </div>
          </li>
          <li>
            <span class="feature-icon"><AppIcon name="check-circle" :size="19" /></span>
            <div>
              <strong>Pantau status tugas</strong>
              <span>Filter tugas selesai dan belum kapan saja.</span>
            </div>
          </li>
          <li>
            <span class="feature-icon"><AppIcon name="shield" :size="19" /></span>
            <div>
              <strong>Data tetap aman</strong>
              <span>Akses dibatasi sesuai peran pengguna.</span>
            </div>
          </li>
        </ul>
      </div>

      <p class="auth__footnote">Dibuat untuk memudahkan kegiatan belajar mengajar.</p>
    </aside>

    <main class="auth__panel">
      <div class="auth__mobile-brand">
        <span class="brand-mark">
          <AppIcon name="check" :size="18" />
        </span>
        <span class="brand-name">TugasKita</span>
      </div>
      <div class="auth__card">
        <slot />
      </div>
    </main>
  </div>
</template>
