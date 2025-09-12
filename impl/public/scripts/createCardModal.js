import ModalComponent from "./modal.js";
import showCardModal from "./showCardModal.js";

// Extends ModalComponent to create modal for creating a card
export default class createCardModal extends ModalComponent{
    #parent_modal = null;
    constructor(openBtn) {
        super(openBtn, 'card_dialog', 'closeCardModal');
        this.#parent_modal = document.getElementById("dialog");
    }

    // Utility method to initialize drag & drop + preview for image input
    uploadImage(container, fileInput, side){
        container.addEventListener('click', () => fileInput.click());
        container.addEventListener('dragover', event =>
        {
            event.preventDefault();
            container.style.opacity = 0.5;
        });
        container.addEventListener('dragleave', event =>
        {
            event.preventDefault();
            container.style.opacity = 1;
        });
        container.addEventListener('drop', event =>
        {
            event.preventDefault();
            container.opacity = 1;
            document.querySelector(`#upload_${side} p`).style.display = 'none';
            fileInput.files = event.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        })

        fileInput.addEventListener('change', () =>{
            let reader = new FileReader();
            reader.onload = e =>{
                container.innerHTML = `<div class="image_container">
                <img src="${e.target.result}" height=100% alt=""/>
                <a href="#" id="remove_image_${side}"><i class="fa-solid fa-xmark"></i></a>
                </div>`;
                document.getElementById(`remove_image_${side}`).addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    container.innerHTML = '<p>Přidejte obrázek</p>';
                    fileInput.value = '';
                });
            }
            reader.readAsDataURL(fileInput.files[0]);
        })
    }

    // Displays a dialog and handles card creating
    async showModal() {
        // Modal form HTML structure
        this.modal.innerHTML = `<button id="closeCardModal">❌</button>
        <h2 id="modalTitle">Nová kartička</h2>
        <form id="modalForm">
            <label for="front_side">Přední strana</label>
            <textarea id="front_side_input" name="front_side" required></textarea>
            <div class="front_image">
                <input type="file" id="card_front_image" accept="image/jpeg, image/png, image/gif, image/jpg">
                <div id="upload_front">
                    <p>Přidejte obrázek</p>
                </div>
            </div>
            <label for="back_side">Zadní strana</label>
            <textarea id="back_side_input" name="back_side" required></textarea>
            <div class="back_image">
                <input type="file" id="card_back_image" accept="image/jpeg, image/png, image/gif, image/jpg">
                <div id="upload_back">
                    <p>Přidejte obrázek</p>
                </div>
            </div>
            <button id="submitBtn" type="submit">Vytvořit</button>
        </form>
        `;
        const frontSideInput = document.getElementById("front_side_input");
        const backSideInput = document.getElementById("back_side_input");
        const targetURL = this.openBtn.dataset.url;
        const submitBtn = document.getElementById('submitBtn');
        const uploadFront = document.querySelector('#upload_front');
        const frontImage = document.querySelector('#card_front_image')
        const uploadBack = document.querySelector('#upload_back');
        const backImage = document.querySelector('#card_back_image')
        // Initialize modal and file upload UI
        this.modal.showModal();
        this.uploadImage(uploadFront, frontImage, 'front');
        this.uploadImage(uploadBack, backImage, 'back');
        // Handle form submission
        submitBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            if (frontSideInput.value.trim() === "" || backSideInput.value.trim() === ""){
                alert('Zadejte popis kartičky');
                return;
            }
            submitBtn.disabled = true;
            const dataInput = {
                front_side: frontSideInput.value,
                back_side: backSideInput.value
            };
            try{
                // Create card with text content
                const res = await fetch(targetURL, {
                    method: 'POST',
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(dataInput)
                });
                if (!res.ok){
                    alert('Chyba vytvoření kartičky');
                    submitBtn.disabled = false;
                    return;
                }
                const data = await res.json();
                const new_element = document.createElement("div");
                new_element.classList.add('card');
                new_element.innerHTML = `
                        <div class="front">
                            <p>${data.front_side}</p>
                        </div>
                        <p class="back">${data.back_side}</p>
                        <a id="openModal"
                           data-value="show_card"
                           data-url="${data._self}"
                           data-control="1"
                           href="#"></a>`;
                const cards = document.querySelector('.cards');
                cards.appendChild(new_element);
                const openCardBtn = new_element.querySelector('#openModal');
                openCardBtn.addEventListener('click', event => {
                    event.preventDefault()
                    new showCardModal(openCardBtn);
                });
                // Upload images if provided
                if (frontImage.files.length > 0 || backImage.files.length > 0){
                    const formData = new FormData();
                    if (frontImage.files.length > 0)
                        formData.append("front_image", frontImage.files[0]);
                    if (backImage.files.length > 0)
                        formData.append("back_image", backImage.files[0]);
                    const image_url = `${data._self}/image`;
                    const imgRes = await fetch(image_url, {
                        method: 'POST',
                        headers: { "Accept": "application/json" },
                        body: formData
                    });
                    if (!imgRes.ok){
                        alert('Chyba přidaní obrázku');
                        return;
                    }
                    const img_data = await imgRes.json();
                    if (img_data.front_image){
                        const card = document.querySelector('.cards').lastElementChild;
                        const new_img = document.createElement("img");
                        new_img.src = `/uploads/img/${img_data.front_image}`;
                        const before_elem = card.querySelector(".front p");
                        before_elem.before(new_img);
                    }
                }
            }
            catch(err){
                alert(err);
                return;
            }
            const deck_info = document.querySelector('.deck_info p');
            const cards_head = document.querySelector('.cards_head p');
            if (deck_info) {
                const text = deck_info.textContent;
                const match = text.match(/\d+/);
                if (match) {
                    const currentCount = parseInt(match[0]);
                    const newCount = currentCount + 1;
                    deck_info.textContent = text.replace(`${currentCount}`, `${newCount}`);
                }
            }
            if (cards_head){
                const text = cards_head.textContent;
                const match = text.match(/\d+/);
                if (match) {
                    const currentCount = parseInt(match[0]);
                    const newCount = currentCount + 1;
                    cards_head.textContent = text.replace(`${currentCount}`, `${newCount}`);
                }
            }
            this.modal.close();
            submitBtn.disabled = false;
        });
    }
}