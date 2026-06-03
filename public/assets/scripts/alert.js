let lastSeenHash = localStorage.getItem("weather_alert_hash") || "";
const toastElement = document.getElementById("liveAlertToast");
const toastBodyContent = document.getElementById("toast-body-content");

const ALERTS_INTERVAL = 10 * 1000;

const toast = new bootstrap.Toast(toastElement, { delay: ALERTS_INTERVAL });

function getAPIBaseUrl() {
  const currentUrl = window.location.href;
  const url = new URL(currentUrl);
  return `${url.protocol}//${url.host}/api/alerts`;
}

function checkForNewAlerts() {
  const API_URL = getAPIBaseUrl();

  fetch(API_URL)
    .then((response) => response.json())
    .then((data) => {
      if (data.hasAlert && data.hash !== lastSeenHash) {
        toastBodyContent.innerHTML = data.html;

        toast.show();

        lastSeenHash = data.hash;
        localStorage.setItem("weather_alert_hash", data.hash);
      }
    })
    .catch((error) => console.error("Помилка нотифікацій:", error));
}

setInterval(checkForNewAlerts, ALERTS_INTERVAL);
checkForNewAlerts();
