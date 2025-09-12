class OtherSide{
    #backSide = null;
    #showButton = null;

    constructor(){
        this.#backSide = document.querySelector('.back');
        this.#showButton = document.getElementById('showOtherSide');
        this.#showOtherSide();
    }
    // Private method to show the back side when the button is clicked
    #showOtherSide(){
        this.#showButton.addEventListener('click', event =>{
            event.preventDefault();
            this.#showButton.style.display = 'none';
            this.#backSide.style.display = 'block';
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new OtherSide();
});