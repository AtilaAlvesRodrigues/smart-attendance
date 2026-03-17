document.addEventListener('DOMContentLoaded', () => {
    // Find all elements with an ID starting with "qrcode-"
    const qrElements = document.querySelectorAll('[id^="qrcode-"]');

    qrElements.forEach(element => {
        const qrContent = element.getAttribute('data-code');
        
        if (qrContent) {
            new QRCode(element, {
                text: qrContent,
                width: 180, 
                height: 180,
                colorDark: "#000000", 
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }
    });
});
