<template>
  <div class="user-detail">
    <div class="header">
      <h2><i class="fa-solid fa-user-gear"></i> Administrar Socio</h2>
      <button @click="$router.push('/users')" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i> Volver
      </button>
    </div>

    <div v-if="user" class="user-card">
      <div class="user-info">
        <div class="info-field">
          <span class="label">Número de socio:</span>
          <span>{{ user.numero_socio }}</span>
        </div>
        <div class="info-field">
          <span class="label">Nombre:</span>
          <span>{{ user.nombre }} {{ user.apellido }}</span>
        </div>
        <div class="info-field">
          <span class="label">Email:</span>
          <span>{{ user.email }}</span>
        </div>
        <div class="info-field">
          <span class="label">Teléfono:</span>
          <span>{{ user.telefono || '—' }}</span>
        </div>
        <div class="info-field">
          <span class="label">Balance:</span>
          <span class="balance">${{ user.dividendos ?? 0 }}</span>
        </div>
        <div class="info-field">
          <span class="label">Administrador:</span>
          <span :class="['admin-status', user.admin ? 'on' : 'off']">
            {{ user.admin ? 'Sí' : 'No' }}
          </span>
        </div>
      </div>

      <div class="actions-bar">
        <button @click="editMode = !editMode" :class="['primary-btn', editMode ? 'cancelar' : '']">
          <i class="fa-solid" :class="editMode ? 'fa-xmark' : 'fa-pen-to-square'"></i>
          {{ editMode ? "Cancelar edición" : "Editar información" }}
        </button>

        <div class="more-wrap" @keydown.esc="showMore = false">
          <button class="more-btn" @click="toggleMore()" :aria-expanded="showMore ? 'true' : 'false'">
            <i class="fa-solid fa-ellipsis-vertical"></i>
          </button>

          <transition name="fade">
            <div v-if="showMore" class="more-menu" @click.stop>
              <button class="menu-item" @click="handleContactFromMenu()" :disabled="!user?.email">
                <i class="fa-solid fa-envelope"></i>
                Contactar
              </button>

              <button class="menu-item danger" @click="handleDeleteFromMenu()">
                <i class="fa-solid fa-trash"></i>
                Eliminar
              </button>
            </div>
          </transition>
        </div>
      </div>


      <transition name="fade">
        <div v-if="editMode" class="edit-section">
          <h3><i class="fa-solid fa-pen"></i> Editar información</h3>

          <div class="form-grid">
            <input v-model="user.numero_socio" placeholder="Número de socio" />
            <input v-model="user.nombre" placeholder="Nombre" />
            <input v-model="user.apellido" placeholder="Apellido" />
            <input v-model="user.telefono" placeholder="Teléfono" />
            <input v-model="user.email" placeholder="Email" />

            <div class="admin-toggle">
              <label>Administrador:</label>
              <label class="switch">
                <input type="checkbox" v-model="user.admin" true-value="1" false-value="0" />
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <button @click="guardarCambios" class="save-btn">
            <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
          </button>
        </div>
      </transition>
    </div>

    <div v-if="filteredTransactions.length" class="transactions-card">
      <h3><i class="fa-solid fa-receipt"></i> Últimas transacciones</h3>
      <div class="transactions-list">
        <div v-for="tx in filteredTransactions" :key="tx.id" class="transaction-item">
          <div class="tx-left">
            <p class="tx-id">Venta #{{ tx.id }}</p>
            <p class="tx-date">{{ formatDate(tx.created_at) }}</p>
            <p class="tx-cajero">Cajero: {{ getCajeroNombre(tx.cajero_id) }}</p>
          </div>
          <div class="tx-right">
            <p class="tx-total">${{ tx.total }}</p>
            <p class="tx-method" :class="tx.metodo_pago">{{ formatMethod(tx.metodo_pago) }}</p>
          </div>
        </div>
      </div>
    </div>

    <p v-else-if="user" class="no-transactions">Este socio aún no tiene transacciones registradas.</p>

    <Teleport to="body">
      <transition name="fade">
        <div v-if="showContact" class="modal-overlay" @click.self="closeContact()">
          <div class="modal-card">
            <div class="modal-header">
              <h3><i class="fa-solid fa-paper-plane"></i> Enviar email</h3>
              <button class="modal-close" @click="closeContact()">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <div class="modal-body">
              <div class="modal-to">
                <span class="to-label">Para:</span>
                <span class="to-value">{{ user.email }}</span>
              </div>

              <input v-model="emailForm.subject" class="modal-input" type="text" placeholder="Asunto" />

              <textarea v-model="emailForm.message" class="modal-textarea" rows="7"
                placeholder="Escribe tu mensaje..."></textarea>

              <p v-if="emailError" class="modal-error">{{ emailError }}</p>
              <p v-if="emailSuccess" class="modal-success">✓ Email enviado</p>
            </div>

            <div class="modal-actions">
              <button class="modal-cancel" @click="closeContact()" :disabled="sendingEmail">
                Cancelar
              </button>
              <button class="modal-send" @click="sendEmailToUser()" :disabled="sendingEmail || !canSendEmail">
                <i class="fa-solid fa-paper-plane"></i>
                {{ sendingEmail ? "Enviando..." : "Enviar" }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <transition name="fade">
        <div v-if="showDelete" class="modal-overlay" @click.self="closeDelete()">
          <div class="modal-card">
            <div class="modal-header">
              <h3><i class="fa-solid fa-triangle-exclamation"></i> Eliminar usuario</h3>
              <button class="modal-close" @click="closeDelete()">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <div class="modal-body">
              <p class="delete-text">
                Vas a eliminar a <strong>{{ user?.nombre }} {{ user?.apellido }}</strong>.
                Esta acción no se puede deshacer.
              </p>

              <p v-if="deleteError" class="modal-error">{{ deleteError }}</p>
            </div>

            <div class="modal-actions">
              <button class="modal-cancel" @click="closeDelete()" :disabled="deletingUser">
                Cancelar
              </button>
              <button class="modal-danger" @click="deleteUser()" :disabled="deletingUser">
                <i class="fa-solid fa-trash"></i>
                {{ deletingUser ? "Eliminando..." : "Eliminar" }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>


  </div>
</template>

<script>
import axios from "axios";
export default {
  beforeUnmount() {
    document.removeEventListener("click", this.onGlobalClick, true)
  },
  data() {
    return {
      user: null,
      showMore: false,
      showDelete: false,
      deletingUser: false,
      deleteError: "",
      transactions: [],
      usersMap: {},
      editMode: false,
      showContact: false,
      sendingEmail: false,
      emailSuccess: false,
      emailError: "",
      emailForm: {
        to: "",
        subject: "",
        message: ""
      }
    };
  },
  computed: {
    filteredTransactions() {
      if (!this.user) return [];
      return this.transactions
        .filter(tx => tx.user_id === this.user.id || tx.cliente_id === this.user.id)
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 10);
    },
    canSendEmail() {
      return !!(this.emailForm.to && this.emailForm.subject.trim() && this.emailForm.message.trim());
    }
  },
  async created() {
    const id = this.$route.params.id;
    const token = localStorage.getItem("auth_token");

    const [userRes, salesRes, usersRes] = await Promise.all([
      axios.get(`${import.meta.env.VITE_APP_URL}/api/users/${id}`, {
        headers: { Authorization: `Bearer ${token}` }
      }),
      axios.get(`${import.meta.env.VITE_APP_URL}/api/sales`, {
        headers: { Authorization: `Bearer ${token}` }
      }),
      axios.get(`${import.meta.env.VITE_APP_URL}/api/users`, {
        headers: { Authorization: `Bearer ${token}` }
      })
    ]);

    this.user = userRes.data;
    this.transactions = salesRes.data;
    this.usersMap = Object.fromEntries(usersRes.data.map(u => [u.id, u]));
    document.addEventListener("click", this.onGlobalClick, true)


  },
  methods: {
    toggleMore() {
      this.showMore = !this.showMore
    },
    closeMore() {
      this.showMore = false
    },
    handleContactFromMenu() {
      this.closeMore()
      this.openContact()
    },
    handleDeleteFromMenu() {
      this.closeMore()
      this.openDelete()
    },
    onGlobalClick(e) {
      if (!this.showMore) return
      const wrap = this.$el?.querySelector(".more-wrap")
      if (!wrap) return
      if (!wrap.contains(e.target)) this.showMore = false
    },

    openDelete() {
      this.deleteError = ""
      this.showDelete = true
    },
    closeDelete() {
      this.showDelete = false
      this.deletingUser = false
      this.deleteError = ""
    },
    async deleteUser() {
      try {
        this.deletingUser = true
        this.deleteError = ""
        const token = localStorage.getItem("auth_token")

        await axios.delete(
          `${import.meta.env.VITE_APP_URL}/api/users/${this.user.id}`,
          { headers: { Authorization: `Bearer ${token}` } }
        )

        this.showDelete = false
        this.$router.push("/users")
      } catch (e) {
        const data = e?.response?.data
        this.deleteError = data?.message || (data?.errors ? Object.values(data.errors).flat().join(" ") : "No se pudo eliminar el usuario.")
      } finally {
        this.deletingUser = false
      }
    },
    getCajeroNombre(id) {
      const u = this.usersMap[id];
      return u ? u.nombre : "Desconocido";
    },
    async guardarCambios() {
      const token = localStorage.getItem("auth_token");
      const payload = { ...this.user, admin: Number(this.user.admin) };
      await axios.put(
        `${import.meta.env.VITE_APP_URL}/api/users/${this.user.id}`,
        payload,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      alert("Cambios guardados correctamente");
      this.editMode = false;
    },
    formatDate(date) {
      return new Date(date).toLocaleString("es-PR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
      });
    },
    formatMethod(method) {
      return method === "athmovil" ? "ATH Móvil" : "Efectivo";
    },
    openContact() {
      this.emailSuccess = false;
      this.emailError = "";
      this.emailForm.to = this.user?.email || "";
      this.emailForm.subject = `JuCoop - Mensaje para ${this.user?.nombre || ""}`.trim();
      this.emailForm.message = "";
      this.showContact = true;
    },
    closeContact() {
      this.showContact = false;
      this.sendingEmail = false;
      this.emailError = "";
    },
    async sendEmailToUser() {
      this.emailSuccess = false
      this.emailError = ""

      if (!this.emailForm.subject.trim() || !this.emailForm.message.trim()) {
        this.emailError = "Completa asunto y mensaje."
        return
      }

      try {
        this.sendingEmail = true
        const token = localStorage.getItem("auth_token")

        await axios.post(
          `${import.meta.env.VITE_APP_URL}/api/send-email`,
          {
            user_ids: [this.user.id],
            subject: this.emailForm.subject,
            body: this.emailForm.message
          },
          { headers: { Authorization: `Bearer ${token}` } }
        )

        this.emailSuccess = true
        setTimeout(() => this.closeContact(), 900)
      } catch (e) {
        const data = e?.response?.data
        this.emailError = data?.message || (data?.errors ? Object.values(data.errors).flat().join(" ") : "No se pudo enviar el email.")
      } finally {
        this.sendingEmail = false
      }
    }

  }
};
</script>

<style scoped>
.user-detail {
  min-height: 100vh;
  background: radial-gradient(circle at top left, #0f2027, #203a43, #2c5364);
  color: #fff;
  padding: 1.2rem;
  font-family: "Inter", sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.header {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  width: 100%;
  max-width: 900px;
  margin-bottom: 1.5rem;
  gap: 0.8rem;
}

.header h2 {
  font-size: 1.6rem;
  color: #9dd86a;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
}

.delete-btn {
  background: rgba(255, 80, 80, 0.14);
  border: 1px solid rgba(255, 99, 99, 0.35);
  color: #ffb0b0;
  border-radius: 10px;
  padding: 0.9rem 1.2rem;
  font-weight: 800;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  transition: 0.35s;
  min-width: 180px;
}

.delete-btn:hover {
  background: rgba(255, 80, 80, 0.22);
  transform: translateY(-1px);
}

.delete-text {
  margin: 0;
  line-height: 1.4;
  color: rgba(255, 255, 255, 0.9);
}

.modal-danger {
  background: linear-gradient(135deg, #ff6b6b, #ff3b3b);
  border: none;
  color: #0f2027;
  border-radius: 12px;
  padding: 0.85rem 1.05rem;
  font-weight: 900;
  cursor: pointer;
  transition: 0.25s;
  display: flex;
  align-items: center;
  gap: 0.55rem;
}

.modal-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}


.back-btn {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 8px;
  padding: 0.6rem 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  transition: 0.25s;
  width: 100%;
  justify-content: center;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.25);
}

.user-card,
.transactions-card {
  background: rgba(255, 255, 255, 0.07);
  border-radius: 14px;
  border-left: 5px solid #9dd86a;
  padding: 1.4rem;
  width: 100%;
  max-width: 900px;
  margin-bottom: 2rem;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
}

.info-field {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.7rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  flex-wrap: wrap;
  gap: 0.4rem;
}

.info-field span:last-child {
  word-break: break-word;
  overflow-wrap: anywhere;
  text-align: right;
}

.label {
  color: #9dd86a;
  font-weight: 600;
  font-size: 0.95rem;
}

.balance {
  font-weight: bold;
  font-size: 1.2rem;
}

.admin-status {
  font-weight: 600;
  padding: 0.3rem 0.7rem;
  border-radius: 8px;
}

.admin-status.on {
  background: rgba(157, 216, 106, 0.2);
  color: #9dd86a;
}

.admin-status.off {
  background: rgba(255, 255, 255, 0.15);
  color: #ccc;
}

.actions-bar {
  margin-top: 1.5rem;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.8rem;
}

.primary-btn {
  flex: 1;
  background: rgba(157, 216, 106, 0.12);
  border: 1px solid rgba(157, 216, 106, 0.4);
  color: #9dd86a;
  border-radius: 12px;
  padding: 0.95rem 1.2rem;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  transition: 0.25s;
  min-height: 48px;
}

.primary-btn:hover {
  background: rgba(157, 216, 106, 0.22);
  transform: translateY(-1px);
}

.primary-btn.cancelar {
  background: rgba(255, 80, 80, 0.12);
  border-color: rgba(255, 99, 99, 0.4);
  color: #ff8b8b;
}

.more-wrap {
  position: relative;
  flex: 0 0 auto;
}

.more-btn {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: rgba(255, 255, 255, 0.9);
  cursor: pointer;
  transition: 0.25s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.more-btn:hover {
  background: rgba(255, 255, 255, 0.14);
  transform: translateY(-1px);
}

.more-menu {
  position: absolute;
  top: calc(100% + 0.55rem);
  right: 0;
  min-width: 210px;
  background: rgba(15, 32, 39, 0.96);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 14px;
  box-shadow: 0 18px 55px rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(10px);
  padding: 0.4rem;
  z-index: 50;
}

.menu-item {
  width: 100%;
  background: transparent;
  border: 0;
  color: rgba(255, 255, 255, 0.92);
  padding: 0.75rem 0.8rem;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  font-weight: 700;
  transition: 0.2s;
  text-align: left;
}

.menu-item:hover {
  background: rgba(255, 255, 255, 0.08);
}

.menu-item:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.menu-item.danger {
  color: #ffb0b0;
}

.menu-item.danger:hover {
  background: rgba(255, 80, 80, 0.14);
}


.edit-btn {
  background: rgba(157, 216, 106, 0.12);
  border: 1px solid rgba(157, 216, 106, 0.4);
  color: #9dd86a;
  border-radius: 10px;
  padding: 0.9rem 1.4rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  transition: 0.35s;
  min-width: 180px;
}

.edit-btn:hover {
  background: rgba(157, 216, 106, 0.25);
  transform: translateY(-1px);
}

.edit-btn.cancelar {
  background: rgba(255, 80, 80, 0.12);
  border-color: rgba(255, 99, 99, 0.4);
  color: #ff8b8b;
}

.contact-btn {
  background: rgba(0, 188, 212, 0.14);
  border: 1px solid rgba(0, 188, 212, 0.35);
  color: #b6f7ff;
  border-radius: 10px;
  padding: 0.9rem 1.2rem;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  transition: 0.35s;
  min-width: 180px;
}

.contact-btn:hover {
  background: rgba(0, 188, 212, 0.24);
  transform: translateY(-1px);
}

.contact-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.save-btn {
  margin-top: 1.5rem;
  width: 100%;
  background: linear-gradient(135deg, #9dd86a, #7ac75d);
  color: #0f2027;
  border: none;
  border-radius: 10px;
  padding: 1rem 1.5rem;
  font-weight: 700;
  font-size: 1rem;
  letter-spacing: 0.3px;
  cursor: pointer;
  transition: 0.35s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  box-shadow: 0 5px 20px rgba(157, 216, 106, 0.25);
}

.edit-section {
  margin-top: 1.5rem;
  background: rgba(0, 0, 0, 0.35);
  padding: 1.2rem;
  border-radius: 10px;
}

.form-grid {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.form-grid input {
  width: 100%;
  padding: 0.8rem;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  font-size: 1rem;
}

.admin-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: #9dd86a;
  font-weight: 600;
}

.switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 26px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #555;
  transition: 0.4s;
  border-radius: 26px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.4s;
  border-radius: 50%;
}

input:checked+.slider {
  background-color: #9dd86a;
}

input:checked+.slider:before {
  transform: translateX(22px);
}

.transactions-card h3 {
  color: #9dd86a;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 1.2rem;
}

.transaction-item {
  background: rgba(0, 0, 0, 0.25);
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 0.8rem;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.tx-id {
  font-weight: 600;
  color: #fff;
}

.tx-date {
  font-size: 0.9rem;
  color: #ccc;
}

.tx-cajero {
  font-size: 0.9rem;
  color: #9dd86a;
  font-weight: 600;
}

.tx-total {
  font-weight: bold;
  font-size: 1.1rem;
}

.tx-method.athmovil {
  color: #00bcd4;
}

.tx-method.efectivo {
  color: #4caf50;
}

.no-transactions {
  opacity: 0.85;
}

.fade-enter-active,
.fade-leave-active {
  transition: 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(6px);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 999999;
}

.modal-card {
  width: 100%;
  max-width: 620px;
  max-height: calc(100vh - 2rem);
  overflow: auto;
  background: rgba(15, 32, 39, 0.92);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 16px;
  box-shadow: 0 18px 55px rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(10px);
  font-family: "Inter", sans-serif;
}


.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h3 {
  margin: 0;
  color: #9dd86a;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.55rem;
}

.modal-close {
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.75);
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0.35rem 0.5rem;
  border-radius: 10px;
  transition: 0.2s;
}

.modal-close:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.modal-body {
  padding: 1rem 1.1rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.modal-to {
  display: flex;
  justify-content: space-between;
  gap: 0.8rem;
  flex-wrap: wrap;
  padding: 0.7rem 0.85rem;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}

.to-label {
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
}

.to-value {
  color: #fff;
  font-weight: 700;
}

.modal-input,
.modal-textarea {
  width: 100%;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  padding: 0.85rem 0.95rem;
  font-size: 1rem;
  outline: none;
}

.modal-textarea {
  resize: vertical;
  min-height: 140px;
}

.modal-error {
  margin: 0;
  color: #ff8b8b;
  font-weight: 600;
}

.modal-success {
  margin: 0;
  color: #9dd86a;
  font-weight: 700;
}

.modal-actions {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.1rem 1.1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  justify-content: flex-end;
  flex-wrap: wrap;
}

.modal-cancel {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.16);
  color: #fff;
  border-radius: 12px;
  padding: 0.85rem 1.05rem;
  font-weight: 700;
  cursor: pointer;
  transition: 0.25s;
}

.modal-cancel:hover {
  background: rgba(255, 255, 255, 0.18);
}

.modal-send {
  background: linear-gradient(135deg, #9dd86a, #7ac75d);
  border: none;
  color: #0f2027;
  border-radius: 12px;
  padding: 0.85rem 1.05rem;
  font-weight: 800;
  cursor: pointer;
  transition: 0.25s;
  display: flex;
  align-items: center;
  gap: 0.55rem;
}

.modal-send:disabled,
.modal-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (min-width: 768px) {
  .header {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }

  .back-btn {
    width: auto;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }

  .user-card,
  .transactions-card {
    padding: 2rem;
  }
}

@media (max-width: 420px) {
  .more-menu {
    right: 0;
    left: 0;
    min-width: unset;
  }
}

@media (max-width: 520px) {
  .transaction-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .tx-right {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: baseline;
  }
}
</style>
