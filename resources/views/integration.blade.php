document.addEventListener("DOMContentLoaded", (event) => {
    const init = () => {
        const button = document.createElement('button');
        button.textContent = 'lollol';
        document.body.appendChild(button);
        button.style.cssText = "position:absolute; bottom:2rem; left: 2rem; width: 80px; height: 80px; background-color:white; color: #ef4444; border-radius:50%; font-size:1.5rem; box-shadow: rgba(0, 0, 0, 0.3) 0px 2px 6px;";
        button.addEventListener('click', () => {
            alert('lollol');
        });
    };

    init();
});