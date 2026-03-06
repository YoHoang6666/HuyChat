<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weather App</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
    }
    input[type="text"] {
      width: 200px;
      padding: 10px;
      margin: 10px;
    }
    button {
      padding: 10px;
      background-color: #4CAF50;
      color: white;
      border: none;
      cursor: pointer;
    }
    #weather {
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <h1>Weather App</h1>
  <input type="text" id="city" placeholder="Enter city name">
  <button onclick="getWeather()">Get Weather</button>
  <div id="weather"></div>

  <script>
    const apiKey = 'f8d8d1e5dc5910cf1f718e086d4e8eca';

    function getWeather() {
      const city = document.getElementById('city').value;
      const url = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`;

      fetch(url)
        .then(response => response.json())
        .then(data => {
          const weather = document.getElementById('weather');
          if (data.cod === '404') {
            weather.innerHTML = `<h2>City not found</h2>`;
          } else {
            weather.innerHTML = `
              <h2>${data.name}, ${data.sys.country}</h2>
              <h3>${data.weather[0].main}</h3>
              <p>Temperature: ${data.main.temp}°C</p>
              <p>Feels like: ${data.main.feels_like}°C</p>
              <p>Humidity: ${data.main.humidity}%</p>
            `;
          }
        })
        .catch(error => {
          console.error('Error fetching weather data:', error);
        });
    }
  </script>
</body>
</html>
