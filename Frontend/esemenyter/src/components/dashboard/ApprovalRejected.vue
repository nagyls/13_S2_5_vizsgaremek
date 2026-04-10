<template>
  <div class="rejection-container">
    <div class="rejection-card">
      <div class="rejection-icon">
        <i class='bx bx-x-circle'></i>
      </div>
      <h1 class="rejection-title">Csatlakozási kérelmét elutasították</h1>
      <p class="rejection-message">
        Sajnálattal értesítjük, hogy az intézménybe való csatlakozási kérelmét elutasították. <br>
        További információért vagy a döntés felülvizsgálatáért, kérjük, vegye fel a kapcsolatot az intézmény adminisztrátorával.
      </p>
      <router-link to="/dashboard?chooseRole=1" class="btn-back-to-dashboard">Új kérelem indítása</router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApprovalRejected',
};
</script>

<style scoped>
.rejection-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  font-family: 'Inter', sans-serif;
  padding: 20px;
}

.rejection-card {
  background: white;
  border-radius: 24px;
  padding: 40px;
  max-width: 500px;
  width: 100%;
  text-align: center;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.5);
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.rejection-icon {
  font-size: 80px;
  color: #ef4444;
  line-height: 1;
  margin-bottom: 20px;
  animation: popIn 0.5s 0.2s ease-out backwards;
}

@keyframes popIn {
  from {
    opacity: 0;
    transform: scale(0.5);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.rejection-title {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 15px 0;
}

.rejection-message {
  font-size: 16px;
  color: #4b5563;
  line-height: 1.6;
  margin: 0 0 30px 0;
}

.btn-back-to-dashboard {
  display: inline-block;
  padding: 12px 30px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border-radius: 50px;
  text-decoration: none;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-back-to-dashboard:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}
</style>
