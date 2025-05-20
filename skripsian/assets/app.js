document.addEventListener("DOMContentLoaded", function() {
    const nextButton = document.querySelector(".btn-next");
    
    if (!nextButton.disabled) {
        nextButton.addEventListener("click", function() {
            alert("Memanggil antrian berikutnya...");
        });
    }
});

function playSound(type, number) {
    let text = "";
    if (type === "repeat") {
        text = `Antrian nomor A ${number}`;
    } else if (type === "next") {
        text = `Selanjutnya antrian nomor A ${number}`;
    }
    let utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = "id-ID";
    speechSynthesis.speak(utterance);
};

