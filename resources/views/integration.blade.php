document.addEventListener("DOMContentLoaded", (event) => {
    const init = () => {

        const iframe = document.createElement("iframe");
        iframe.setAttribute("src", "https://lollol.ai");
        iframe.setAttribute("id", "lollol_integration_iframe");
        iframe.style.cssText = "display:none; width: 400px;height: 50%;position:absolute;bottom:  1rem;right: calc(2rem + 90px);border: 1px solid #ccc;border-radius: 10px;min-height: 300px;";
        document.body.appendChild(iframe);

        const button = document.createElement('button');
        button.textContent = 'lollol';
        button.setAttribute("id", "lollol_integration_button");
        document.body.appendChild(button);
        button.style.cssText = "position:absolute; bottom:2rem; right: 2rem; width: 70px; height: 70px; background-color:white; color: #ef4444; border-radius:50%; border-color:#ccc; font-size:1.3rem; box-shadow: rgba(0, 0, 0, 0.3) 0px 2px 6px;";
        button.addEventListener('click', () => {
            const button = document.getElementById("lollol_integration_button");
            const iframe = document.getElementById("lollol_integration_iframe");
            if (button.textContent == 'lollol') {
                iframe.style.display = "block";
                button.textContent = "✗";
            }
            else {
                iframe.style.display = "none";
                button.textContent = "lollol";
            }
            
            
        });
    };

    init();
});