document.addEventListener("DOMContentLoaded", () => {
  const snowflakesCount = 100;
  const body = document.body;

  for (let i = 0; i < snowflakesCount; i++) {
    const snowflake = document.createElement("div");
    snowflake.className = "snowflake";
    snowflake.textContent = "❄";
    snowflake.style.left = `${Math.random() * 100}vw`;
    snowflake.style.fontSize = `${Math.random() * 1.5 + 0.5}em`;
    snowflake.style.animationDuration = `${Math.random() * 3 + 2}s, ${
      Math.random() * 5 + 3
    }s`;
    snowflake.style.animationDelay = `${Math.random() * 5}s, ${
      Math.random() * 5
    }s`;
    body.appendChild(snowflake);

    // Remove snowflake after animation
    snowflake.addEventListener("animationend", () => snowflake.remove());
  }
});
