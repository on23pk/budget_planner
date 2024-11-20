<template>
  <div class="container mt-4">
    <h2>Transaktion bearbeiten</h2>
    <form @submit.prevent="updateTransaction">
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input
          type="text"
          id="name"
          v-model="transaction.name"
          class="form-control"
          required
        />
      </div>

      <div class="mb-3">
        <label for="amount" class="form-label">Betrag</label>
        <input
          type="number"
          step="0.01"
          id="amount"
          v-model="transaction.amount"
          class="form-control"
          required
        />
      </div>

      <div class="mb-3">
        <label for="category" class="form-label">Kategorie</label>
        <select
          id="category"
          v-model="transaction.category"
          class="form-select"
          required
        >
          <option value="Einnahme">Einnahme</option>
          <option value="Ausgabe">Ausgabe</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="transaction_date" class="form-label">Datum</label>
        <input
          type="date"
          id="transaction_date"
          v-model="transaction.transaction_date"
          class="form-control"
          required
        />
      </div>

      <button type="submit" class="btn btn-primary">Speichern</button>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      transaction: {
        id: "",
        name: "",
        amount: "",
        category: "",
        transaction_date: "",
      },
    };
  },
  mounted() {
    this.fetchTransaction(); // Lade die Transaktion, wenn die Komponente gemountet wird
  },
  methods: {
    // Holt die Transaktionsdaten vom Backend
    async fetchTransaction() {
      try {
        const response = await fetch(
          `http://localhost/budget_planner/backend/budget.php?id=${this.$route.params.id}`,
          {
            method: "GET",
            headers: {
              "Content-Type": "application/json",
            },
            credentials: "include",
          }
        );

        if (!response.ok) {
          throw new Error(`HTTP-Fehler! Status: ${response.status}`);
        }

        const result = await response.json();
        if (result.status === "success" && result.data) {
          this.transaction = result.data; // Weist die empfangenen Daten der Transaktion zu
        } else {
          console.error("Fehler beim Laden der Transaktion:", result.message);
          alert(result.message || "Unbekannter Fehler beim Laden der Daten.");
        }
      } catch (error) {
        console.error("Fehler beim Abrufen der Transaktion:", error);
        alert("Fehler beim Abrufen der Transaktion.");
      }
    },

    // Aktualisiert die Transaktionsdaten
    async updateTransaction() {
  try {
    console.log("Gesendete Daten:", this.transaction); // Debug-Ausgabe
    const response = await fetch("http://localhost/budget_planner/backend/budget.php", {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(this.transaction),
      credentials: "include",
    });

    if (!response.ok) {
      throw new Error(`HTTP-Fehler! Status: ${response.status}`);
    }

    const result = await response.json();
    if (result.status === "success") {
      this.$router.push("/transactions");
    } else {
      console.error("Fehler beim Speichern:", result.message);
      alert(result.message || "Unbekannter Fehler beim Speichern.");
    }
  } catch (error) {
    console.error("Fehler beim Bearbeiten:", error);
    alert("Fehler beim Bearbeiten der Transaktion.");
  }
}
    },
};
</script>

<style scoped>
.container {
  max-width: 600px;
}
</style>
