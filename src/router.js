import { createRouter, createWebHistory } from 'vue-router';
import LoginRegisterForm from './components/LoginRegisterForm.vue';
import Transactions from './components/Transactions.vue';
import BudgetForm from './components/BudgetForm.vue';
import EditTransactions from './components/EditTransactions.vue'; // Importiere die EditTransaction-Komponente

// Simulierter Login-Check (du könntest dies durch einen API-Call ersetzen)
const isAuthenticated = () => {
  return !!localStorage.getItem('user'); // Beispiel: Nutzer wird als eingeloggt betrachtet, wenn 'user' gesetzt ist
};

const routes = [
  {
    path: '/',
    component: LoginRegisterForm, // Login/Register-Seite
    alias: '/login', // Alias für die Login-Seite
  },
  {
    path: '/transactions',
    name: 'Transactions',
    component: Transactions, // Transaktionsliste
    meta: { requiresAuth: true }, // Auth erforderlich
  },
  {
    path: '/add-transaction',
    name: 'AddTransaction',
    component: BudgetForm, // Formular zum Hinzufügen einer Transaktion
    meta: { requiresAuth: true }, // Auth erforderlich
  },
  {
    path: '/edit-transaction/:id', // Route zum Bearbeiten einer Transaktion mit ID als Parameter
    name: 'EditTransaction',
    component: EditTransactions, // Komponente für die Bearbeitung
    props: true, // Übergibt den Parameter `id` als Prop an die Komponente
    meta: { requiresAuth: true }, // Auth erforderlich
  },
];


const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation Guard für Routen mit `requiresAuth`
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !isAuthenticated()) {
    next('/'); // Weiterleitung zur Login-Seite, wenn nicht eingeloggt
  } else {
    next(); // Weiterleitung zur Zielroute
  }
});

export default router;
