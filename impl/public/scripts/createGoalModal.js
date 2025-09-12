import ModalComponent from "./modal.js";

// Extends ModalComponent to create modal for creating a goal
export default class createGoalModal extends ModalComponent {
    constructor(openBtn) {
        super(openBtn, 'dialog', 'closeModal')
    }

    // Displays a dialog and handles goal creating
    showModal() {
        // Modal form HTML structure
        this.modal.innerHTML = `<button id="closeModal">❌</button>
        <h2 id="modalTitle">Nová cíl</h2>
        <form id="modalForm">
            <label for="target_cards">Počet kartiček k naučení</label>
            <input type="number" id="target_cards" name="target_cards">
    
            <label for="target_tests">Počet testů k absolvování</label>
            <input type="number" id="target_tests" name="target_tests">
            <p>Za úspěšný se považuje test, pokud je správně zodpovězeno alespoň tři čtvrtiny otázek </p>
    
            <button id="submitBtn" type="submit">Nastavit</button>
        </form>
        `;
        const targetCardsInput = document.getElementById("target_cards");
        targetCardsInput.min = 1;
        const targetTestsInput = document.getElementById("target_tests");
        targetTestsInput.min = 1;
        const targetURL = this.openBtn.dataset.url;
        const submitBtn = document.getElementById('submitBtn');
        this.modal.showModal();
        // Handle form submission
        submitBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            if ((targetCardsInput.value === "" || targetCardsInput.value <= 0) &&
                (targetTestsInput.value === "" || targetTestsInput.value <= 0)){
                alert('Musíte zadat alespoň jeden cíl větší než 0');
                return;
            }
            const dataInput = {
                target_cards: +targetCardsInput.value,
                target_tests: +targetTestsInput.value
            }
            // Sends POST request to create the goal and updates the UI
            try{
                const res = await fetch(targetURL, {
                    method: 'POST',
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(dataInput)
                });
                if (!res.ok){
                    alert('Chyba vytvoření cíle');
                    return;
                }
                const data = await res.json();
                const end_date = new Date(data.end_date);
                const d = end_date.getDate().toString().padStart(2, '0');
                const m = (end_date.getMonth() + 1).toString().padStart(2, '0');
                const y = end_date.getFullYear();
                const actualGoal = this.openBtn.parentElement;
                actualGoal.innerHTML = `<h3>Aktuální cíl</h3>
                <p>Do: <time datetime="${data.end_date}">${d}.${m}.${y}</time></p>`
                const afterElement = actualGoal.querySelector('h3');
                if (data.target_tests){
                    const to_study = document.createElement('p');
                    to_study.textContent = `Úspěšně dokončit ${data.target_tests} testů`;
                    const progress_container = document.createElement('div');
                    progress_container.classList.add('progress_container');
                    progress_container.title = 'Počet dokončených testů za týden'
                    const progress_bar = document.createElement('div');
                    progress_bar.classList.add('progress_bar');
                    progress_bar.style.width = '0%';
                    progress_container.appendChild(progress_bar);
                    const note = document.createElement('p');
                    note.textContent= 'Za úspěšný se považuje test, pokud je správně zodpovězeno alespoň tři čtvrtiny otázek';
                    afterElement.after(to_study);
                    to_study.after(note);
                    note.after(progress_container);
                }
                if (data.target_cards){
                    const to_study = document.createElement('p');
                    to_study.textContent = `Prostudovat ${data.target_cards} kartiček`;
                    const progress_container = document.createElement('div');
                    progress_container.classList.add('progress_container');
                    progress_container.title = 'Počet prostudovaných kartiček za týden';
                    const progress_bar = document.createElement('div');
                    progress_bar.classList.add('progress_bar');
                    progress_bar.style.width = '0%';
                    progress_container.appendChild(progress_bar);
                    afterElement.after(to_study);
                    to_study.after(progress_container);
                }
            }
            catch(err){
                alert(err);
                return;
            }
            this.modal.close();
        });
    }
}