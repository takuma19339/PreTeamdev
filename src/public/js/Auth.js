// パスワード欄の目のボタンで、表示/非表示を切り替える
document.querySelectorAll("[data-toggle-password]").forEach((btn) => {
  btn.addEventListener("click", () => {
    const input = document.getElementById(btn.dataset.togglePassword);
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    btn.setAttribute("aria-pressed", String(show));
    btn.setAttribute("aria-label", show ? "パスワードを隠す" : "パスワードを表示する");
  });
});