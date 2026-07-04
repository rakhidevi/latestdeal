let lastFoundDeal = null;

chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
  if (request.type === "CHECK_DEAL") {
    const title = request.title;
    // Replace with actual production URL when deployed
    const apiUrl = `http://localhost:8000/api/deals/search?q=${encodeURIComponent(title)}`;
    
    fetch(apiUrl)
      .then(res => res.json())
      .then(data => {
        if (data && data.deals && data.deals.length > 0) {
          lastFoundDeal = data.deals[0];
          // Update extension icon badge to notify user
          chrome.action.setBadgeText({ text: '1', tabId: sender.tab.id });
          chrome.action.setBadgeBackgroundColor({ color: '#FF0000', tabId: sender.tab.id });
        } else {
          lastFoundDeal = null;
        }
      })
      .catch(err => console.error("LatestDeal API Error:", err));
  } else if (request.type === "GET_DEAL") {
    sendResponse({ deal: lastFoundDeal });
  }
  return true;
});
