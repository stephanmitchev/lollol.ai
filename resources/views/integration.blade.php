document.addEventListener("DOMContentLoaded", (event) => {
    const init = () => {

        const iframe = document.createElement("iframe");
        iframe.setAttribute("src", "https://lollol.ai");
        iframe.setAttribute("id", "lollol_integration_iframe");
        iframe.style.cssText = "display:none; width: 400px;height: 50%;position:absolute;bottom:  1rem;right: calc(2rem + 100px);border: 1px solid #ccc;border-radius: 10px;min-height: 300px;";
        document.body.appendChild(iframe);

        const button = document.createElement('button');
        button.textContent = 'lollol';
        document.body.appendChild(button);
        button.style.cssText = "position:absolute; bottom:2rem; right: 2rem; width: 80px; height: 80px; background-color:white; color: #ef4444; border-radius:50%; border-color:#ccc; font-size:1.5rem; box-shadow: rgba(0, 0, 0, 0.3) 0px 2px 6px;";
        button.addEventListener('click', () => {
            //alert('lollol');
            document.getElementById("lollol_integration_iframe").style.display = "block";
        });
    };

    init();
});