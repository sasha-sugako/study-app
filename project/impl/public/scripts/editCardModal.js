import ModalComponent from "./modal.js";

// Extends ModalComponent to create modal for editing a card
export default class editCardModal extends ModalComponent{
    #parent_modal = null;
    #parentBtn = null;
    constructor(openBtn, parentBtn) {
        super(openBtn, 'card_dialog', 'closeCardModal')
        this.#parentBtn = parentBtn;
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
    // Displays a dialog and handles card editing
    async showModal() {
        // Modal form HTML structure
        this.modal.innerHTML = `<button id="closeCardModal">❌</button>
        <h2 id="modalTitle">Upravit kartičku</h2>
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
        // Load existing card data
        try {
            const res = await fetch(targetURL, {
                method: "GET",
                headers: {"Accept": "application/json"}
            });
            const data = await res.json();
            frontSideInput.value = data.front_side;
            backSideInput.value = data.back_side;
            submitBtn.textContent = 'Upravit';
            if (data.front_image) {
                document.querySelector('#upload_front p').style.display = 'none';
                uploadFront.innerHTML = `<div class="image_container">
                   <img src="/uploads/img/${data.front_image}" height=100% alt=""/>
                   <a href="#" id="remove_front_image" class="remove_image"><i class="fa-solid fa-xmark"></i></a>
                   </div>`;
            }
            if (data.back_image) {
                document.querySelector('#upload_back p').style.display = 'none';
                uploadBack.innerHTML = `<div class="image_container">
                   <img src="/uploads/img/${data.back_image}" height=100% alt=""/>
                   <a href="#" id="remove_back_image" class="remove_image"><i class="fa-solid fa-xmark"></i></a>
                   </div>`;
            }
        }
        catch (err) {
            alert(err);
            return;
        }
        // Initialize modal and file upload UI
        this.modal.showModal();
        const remove_front_image = document.getElementById('remove_front_image');
        let to_remove_front_image = false;
        let to_remove_back_image = false;
        const remove_back_image = document.getElementById('remove_back_image');
        if (remove_front_image){
            remove_front_image.addEventListener('click', event=> {
                event.preventDefault();
                event.stopPropagation();
                to_remove_front_image = true;
                uploadFront.innerHTML = `<p>Přidejte obrázek</p>`;
            })
        }
        if (remove_back_image){
            remove_back_image.addEventListener('click', event=> {
                event.preventDefault();
                event.stopPropagation();
                to_remove_back_image = true;
                uploadBack.innerHTML = `<p>Přidejte obrázek</p>`;
            })
        }
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
                // Delete removed images
                if (to_remove_front_image){
                    await fetch (`${targetURL}/front_image`,{
                        method: "DELETE",
                        headers: {"Accept": "application/json"}
                    });
                }
                if (to_remove_back_image){
                    await fetch (`${targetURL}/back_image`,{
                        method: "DELETE",
                        headers: {"Accept": "application/json"}
                    });
                }
                // Update text content
                const res = await fetch(targetURL, {
                    method: 'PUT',
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
                const parentFrontText = this.#parentBtn.parentElement.querySelector('.front p');
                const parentBackText = this.#parentBtn.parentElement.querySelector('.back');
                const frontSideText = this.#parent_modal.querySelector('.front p');
                const backSideText = this.#parent_modal.querySelector('.back p');
                frontSideText.textContent = data.front_side;
                backSideText.textContent = data.back_side;
                parentFrontText.textContent = data.front_side;
                parentBackText.textContent = data.back_side;
                if (!data.front_image && this.#parent_modal.querySelector('.front img')){
                    const parentFront = this.#parentBtn.parentElement.querySelector('.front');
                    const front = this.#parent_modal.querySelector('.front');
                    const frontImg = front.querySelector('img');
                    front.removeChild(frontImg);
                    const parentFrontImg = parentFront.querySelector('img');
                    parentFront.removeChild(parentFrontImg);
                }
                if (!data.back_image && this.#parent_modal.querySelector('.back img')){
                    const back = this.#parent_modal.querySelector('.back');
                    const backImg = back.querySelector('img');
                    back.removeChild(backImg);
                }
                // Upload new images if provided
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
                    // Update image in modal and parent
                    if (img_data.front_image){
                        const img = this.#parent_modal.querySelector('.front img');
                        if (img){
                            const parent_img = this.#parentBtn.parentElement.querySelector('.front img');
                            img.src = `/uploads/img/${img_data.front_image}`;
                            parent_img.src = `/uploads/img/${img_data.front_image}`;
                        }
                        else{
                            const parentFront = this.#parentBtn.parentElement.querySelector('.front p');
                            const front = this.#parent_modal.querySelector('.front p');
                            const new_img = document.createElement("img");
                            new_img.src = `/uploads/img/${img_data.front_image}`;
                            const new_img_clone = new_img.cloneNode();
                            front.before(new_img);
                            parentFront.before(new_img_clone);
                        }
                    }
                    if (img_data.back_image){
                        const img = this.#parent_modal.querySelector('.back img');
                        if (img){
                            img.src = `/uploads/img/${img_data.back_image}`;
                        }
                        else {
                            const back = this.#parent_modal.querySelector('.back p');
                            const new_img = document.createElement("img");
                            new_img.src = `/uploads/img/${img_data.back_image}`;
                            back.before(new_img);
                        }
                    }
                }
            }
            catch(err){
                alert(err);
                return;
            }
            this.modal.close();
            submitBtn.disabled = false;
        });
    }
}