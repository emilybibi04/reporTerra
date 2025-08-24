import { initializeApp } from "firebase/app";
import { getDatabase, ref, set, get, child, push } from "firebase/database";

const firebaseConfig = {
  apiKey: "AIzaSyBbYVBmt_-_leLmPG9bD117gO3wVuc-YBE",
  authDomain: "reporterra-433b5.firebaseapp.com",
  databaseURL: "https://reporterra-433b5-default-rtdb.firebaseio.com",
  projectId: "reporterra-433b5",
  storageBucket: "reporterra-433b5.firebasestorage.app",
  messagingSenderId: "877249339139",
  appId: "1:877249339139:web:2128c909457398ec191336",
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

export { db, ref, set, get, child, push };
