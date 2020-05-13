let id = document.getElementById('topicId').value;
//console.log(location);
const origin = location.origin; // http://localhost
const appId = location.pathname.split('/')[1]; //monitor-beta-prochile


const baseUrlApi = `${origin}/${appId}/web/topic/api/topic/`;

