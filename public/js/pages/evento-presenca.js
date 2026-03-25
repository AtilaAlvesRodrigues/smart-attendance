// Gestão de Sessão de Evento - Attendance
let visitors = [];
let eventQrGenerated = false;
let currentProfInfo = { name: 'Professor', email: 'professor@smart.edu' };

function initEventPresenca(checkinUrl, profInfo) {
    if (profInfo) currentProfInfo = profInfo;
    
    // Generate QR
    const qrContainer = document.getElementById("qrcode");
    if (qrContainer) {
        new QRCode(qrContainer, {
            text: checkinUrl,
            width: 200, height: 200,
            colorDark: "#000000", colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    // Refresh loop
    setInterval(updateList, 2000);
    updateList();
}

function copyLink() {
    const input = document.getElementById('copy-link');
    const toast = document.getElementById('copy-toast');

    input.select();
    document.execCommand('copy');
    
    // Show Premium Toast
    if (toast) {
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }
}

function maskEmail(email) {
    const [user, domain] = email.split('@');
    return user.charAt(0) + '***@' + domain;
}

function updateList() {
    const stored = JSON.parse(localStorage.getItem('event_checkins_data') || '[]');
        visitors = stored;
        const container = document.getElementById('visitor-list');
        const count = document.getElementById('visitor-count');
        const empty = document.getElementById('empty-state');

        container.innerHTML = '';
        count.innerText = visitors.length;

        if (visitors.length > 0) {
            if (empty) empty.style.display = 'none';
            
            visitors.forEach(v => {
                const tr = document.createElement('tr');
                tr.className = 'animate-reveal border-b border-white/5';
                
                const tdName = document.createElement('td');
                tdName.className = 'py-4 px-2 font-bold text-white text-sm';
                tdName.textContent = v.name;
                
                const tdEmail = document.createElement('td');
                tdEmail.className = 'py-4 px-2 text-xs font-mono text-white/50';
                tdEmail.textContent = maskEmail(v.email);
                
                tr.appendChild(tdName);
                tr.appendChild(tdEmail);
                container.appendChild(tr);
            });
        } else {
            if (empty) {
                empty.style.display = 'table-row';
                container.appendChild(empty);
            }
        }
}

function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Header Branding
    doc.setFillColor(15, 15, 15);
    doc.rect(0, 0, 210, 45, 'F');
    
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(24);
    doc.setFont("helvetica", "bold");
    doc.text("SMART ATTENDANCE", 14, 20);
    
    doc.setFontSize(10);
    doc.setFont("helvetica", "normal");
    doc.text("RELATÓRIO DE PRESENÇA — PALESTRA / EVENTO", 14, 28);
    
    // Gestor Info Section
    doc.setFillColor(245, 245, 247);
    doc.rect(14, 55, 182, 25, 'F');
    
    doc.setTextColor(0, 0, 0);
    doc.setFontSize(11);
    doc.setFont("helvetica", "bold");
    doc.text("DADOS DO GESTOR RESPONSÁVEL", 20, 65);
    
    doc.setFont("helvetica", "normal");
    doc.setFontSize(10);
    doc.text("NOME: " + currentProfInfo.name, 20, 72);
    doc.text("E-MAIL: " + currentProfInfo.email, 20, 77);
    
    // Table
    const tableData = visitors.map(v => [v.name, v.email, 'CONFIRMADO']);
    
    doc.autoTable({
        head: [['NOME COMPLETO', 'E-MAIL DE CONTATO', 'STATUS']],
        body: tableData,
        startY: 85,
        theme: 'striped',
        headStyles: { 
            fillColor: [0, 0, 0], 
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            halign: 'center'
        },
        alternateRowStyles: { fillColor: [250, 250, 252] },
        styles: { fontSize: 9, cellPadding: 4 },
        columnStyles: {
            2: { halign: 'center' }
        }
    });
    
    // Footer
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(150);
        doc.text("Gerado automaticamente pelo Smart Attendance em: " + new Date().toLocaleString(), 14, 285);
        doc.text("Página " + i + " de " + pageCount, 180, 285);
    }
    
    doc.save("Relatorio_Palestra_" + currentProfInfo.name.replace(/\s+/g, '_') + ".pdf");
}

function closeSession() {
    const modal = document.getElementById('confirm-overlay');
    if (modal) modal.classList.remove('hidden');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-overlay');
    if (modal) modal.classList.add('hidden');
}

async function executeCloseSession() {
    closeConfirmModal();
    
    // Notify backend and send email summary (simulated via controller)
    try {
        await fetch('/professor/evento/encerrar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ participantes: visitors })
        });
    } catch (e) {
        console.warn("Email simulation error, continuing cleanup.");
    }
    
    // Clear attendance data for next session
    localStorage.removeItem('event_checkins');
    localStorage.removeItem('event_checkins_data');
    localStorage.removeItem('already_checked_in');
    
    const successOverlay = document.getElementById('close-overlay');
    if (successOverlay) successOverlay.classList.remove('hidden');
}
