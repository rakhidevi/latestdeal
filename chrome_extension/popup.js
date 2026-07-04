document.addEventListener('DOMContentLoaded', () => {
  chrome.runtime.sendMessage({ type: "GET_DEAL" }, (response) => {
    const content = document.getElementById('content');
    if (response && response.deal) {
      const deal = response.deal;
      // Since it's a localhost link, in production it would be the real domain
      const dealUrl = `http://localhost:8000/deal/${deal.id}`;
      
      content.innerHTML = `
        <div class="deal-card">
          <div class="title">${deal.title}</div>
          <div class="price">₹${deal.discounted_price}</div>
          <a href="${dealUrl}" target="_blank" class="btn">View on LatestDeal</a>
        </div>
      `;
    } else {
      content.innerHTML = `<div class="no-deal">No better deals found on LatestDeal for this product.</div>`;
    }
  });
});
