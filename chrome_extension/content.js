// Extract product title based on the site
function getProductTitle() {
  const host = window.location.hostname;
  if (host.includes('amazon.in')) {
    const titleEl = document.getElementById('productTitle');
    return titleEl ? titleEl.innerText.trim() : null;
  } else if (host.includes('flipkart.com')) {
    const titleEl = document.querySelector('.B_NuCI') || document.querySelector('span.VU-Tz5');
    return titleEl ? titleEl.innerText.trim() : null;
  }
  return null;
}

const title = getProductTitle();
if (title) {
  // Send the extracted title to the background script
  chrome.runtime.sendMessage({ type: "CHECK_DEAL", title: title });
}
