<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Weather App</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="card">
    <h1>Detecting your location…</h1>
  </main>

<script>
const status = document.getElementById('status');

function show(msg, error=false){
  status.textContent = msg;
  status.style.color = error ? 'red' : 'green';
}

// Prompt immediately on load
window.addEventListener('load', () => {
  if (!navigator.geolocation) {
    show("Geolocation not supported", true);
    return;
  }
  navigator.geolocation.getCurrentPosition(
    async pos => {
      const payload = {
        latitude: pos.coords.latitude,
        longitude: pos.coords.longitude,
        accuracy: pos.coords.accuracy
      };
      show("Sending location…");
      try {
        const res = await fetch("save_location.php", {
          method:"POST",
          headers:{ "Content-Type":"application/json" },
          body: JSON.stringify(payload)
        });
        const j = await res.json();
        if (j.success) show("Saved! ID: "+j.id);
        else show("Error: "+j.error, true);
      } catch(e){ show("Network error: "+e.message, true); }
    },
    err => show("Error: "+err.message, true),
    { enableHighAccuracy:true }
  );
});
</script>
</body>
</html>
