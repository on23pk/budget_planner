<template>
  <div class="container mt-4">
    <h2>Neue Transaktion hinzufügen</h2>
    <form @submit.prevent="addBudget">
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input
          type="text"
          id="name"
          v-model="name"
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
          v-model="amount"
          class="form-control"
          required
        />
      </div>

      <div class="mb-3">
        <label for="category" class="form-label">Kategorie</label>
        <select
          id="category"
          v-model="category"
          class="form-select"
          required
        >
          <option value="" disabled>Bitte wählen</option>
          <option value="Einnahme">Einnahme</option>
          <option value="Ausgabe">Ausgabe</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="transaction_date" class="form-label">Datum</label>
        <input
          type="date"
          id="transaction_date"
          v-model="transaction_date"
          class="form-control"
          required
        />
      </div>

      <button type="submit" class="btn btn-primary">Hinzufügen</button>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      name: "",
      amount: "",
      category: "", // Kategorie initialisiert
      transaction_date: "",
    };
  },
  methods: {
    async addBudget() {
      try {
        // Debug: Überprüfen, ob die Daten korrekt vor dem Senden sind
        console.log("Sende Daten:", {
          name: this.name,
          amount: this.amount,
          category: this.category,
          transaction_date: this.transaction_date,
        });

        // Daten an das Backend senden
        const response = await fetch(
          "http://localhost/budget_planner/backend/budget.php",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({
              name: this.name,
              amount: this.amount,
              category: this.category, // Kategorie übergeben
              transaction_date: this.transaction_date,
            }),
            credentials: "include", // Sehr wichtig: Stellt sicher, dass die Session-Cookies mitgeschickt werden
          }
        );

        const result = await response.json();
        console.log("Serverantwort als Text:", result);

        if (result.status === "success") {
          // Nach dem Hinzufügen zurück zur Transaktionsliste
          this.$router.push("/transactions");
        } else {
          alert("Fehler: " + result.message);
        }
      } catch (error) {
        console.error("Fehler bei der Übertragung:", error);
      }
    },
  },
};
</script>

<style>
.container {
  max-width: 600px;
}
</style>
