// js/navigation.js
document.addEventListener("DOMContentLoaded", () => {
  const nav = document.createElement("nav");
  nav.innerHTML = `
    <ul style="list-style: none; display: flex; gap: 1rem; padding: 0; margin-bottom: 2rem;">
      <li><a href="/index.html">Home</a></li>
      <li><a href="/articles/history.html">History</a></li>
      <li><a href="/articles/observation.html">Observation</a></li>
    </ul>
  `;

  document.body.insertBefore(nav, document.body.firstChild);
});