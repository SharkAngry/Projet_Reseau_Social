const API_BASE = new URL("api/", document.baseURI).href.replace(/\/$/, "");
async function apiRequest(
  endpoint,
  method = "GET",
  body = null,
  isFormData = false,
) {
  const token = sessionStorage.getItem("token");

  const headers = {};
  if (token) {
    headers["Authorization"] = "Bearer " + token;
  }

  const options = { method, headers };

  if (body) {
    if (isFormData) {
      options.body = body;
    } else {
      headers["Content-Type"] = "application/json";
      options.body = JSON.stringify(body);
    }
  }

  const response = await fetch(`${API_BASE}/${endpoint}`, options);
  const contentType = response.headers.get("content-type") || "";
  const data = contentType.includes("application/json")
    ? await response.json()
    : { error: await response.text() };

  if (!response.ok) {
    throw new Error(data.error || data.message || "Une erreur est survenue");
  }

  return data;
}
