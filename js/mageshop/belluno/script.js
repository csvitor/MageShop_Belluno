const Belluno = {
    async update(controller_belluno){
        document.querySelector( "body" ).insertAdjacentHTML(
        'afterbegin',
         `<style>
            .fulldisplay {
                position: fixed;
                height: 100vh;
                background: #01010161;
                z-index: 999;
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .lds-ring {
                display: inline-block;
                position: relative;
                width: 80px;
                height: 80px;
            }
            .lds-ring div {
                box-sizing: border-box;
                display: block;
                position: absolute;
                width: 64px;
                height: 64px;
                margin: 8px;
                border: 8px solid #fff;
                border-radius: 50%;
                animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
                border-color: #fff transparent transparent transparent;
            }
            .lds-ring div:nth-child(1) {
                animation-delay: -0.45s;
            }
            .lds-ring div:nth-child(2) {
                animation-delay: -0.3s;
            }
            .lds-ring div:nth-child(3) {
                animation-delay: -0.15s;
            }
            @keyframes lds-ring {
                0% {
                transform: rotate(0deg);
                }
                100% {
                transform: rotate(360deg);
                }
            }
        </style>
        <div class="fulldisplay">
            <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
        </div>`);
        const res = await this.consultPayment(controller_belluno)
        window.location.reload();
    },
    async consultPayment(url) {
        const options = {
            method: 'GET',
            headers: {
              accept: 'application/json',
            }
        };
        let results = await fetch(url, options).then((res) => {
            return res.json();
        })  
        return results;
    }
}


function copiarTextoPix(e) {
    var textoCopiado = document.getElementById("pixTextBelluno");
    textoCopiado.select();
    document.execCommand("copy");
    document.getElementById("pixTextBelluno").blur();
    setTimeout(function(){
        e.style.backgroundColor = "#000";
    }, 6000)
    e.style.backgroundColor = "#117e31";
}