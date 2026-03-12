// Modal Logic
const modal = document.getElementById('info-modal');
const modalBody = document.getElementById('modal-body');
const modalTitle = document.getElementById('modal-title');
const modalContent = document.getElementById('modal-content-container');

// Security: Prevent XSS
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function openModal(type, data) {
    let content = '';
    let title = '';

    const renderMaterias = (materias) => {
        if (!materias || materias.length === 0) {
            return '<p class="text-white/20 italic text-xs">Nenhuma matéria vinculada.</p>';
        }
        return `
            <div class="mt-4">
                <p class="text-[10px] text-white/40 uppercase font-black tracking-widest mb-2">Matérias Matriculadas</p>
                <div class="flex flex-wrap gap-2">
                ${materias.map(m => `
                    <span class="bg-blue-500/10 text-blue-300 text-[11px] font-bold px-3 py-1 rounded-lg border border-blue-500/20">
                        ${escapeHtml(m.nome)}
                    </span>
                `).join('')}
                </div>
            </div>
        `;
    };

    const renderProfessores = (professores) => {
        if (!professores || professores.length === 0) {
            return '<p class="text-white/20 italic text-xs">Sem professores vinculados.</p>';
        }
        return `
            <div class="mt-4">
                <p class="text-[10px] text-white/40 uppercase font-black tracking-widest mb-2">Corpo Docente</p>
                <div class="flex flex-wrap gap-2">
                ${professores.map(p => `
                    <span class="bg-indigo-500/10 text-indigo-300 text-[11px] font-bold px-3 py-1 rounded-lg border border-indigo-500/20">
                        ${escapeHtml(p.nome)}
                    </span>
                `).join('')}
                </div>
            </div>
        `;
    };

    if (type === 'professor') {
        title = 'DETALHES DO PROFESSOR';
        content = `
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white/10 p-4 rounded-2xl border border-white/20">
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-1">Nome Completo</p>
                        <p class="text-white font-bold tracking-tight">${escapeHtml(data.nome)}</p>
                    </div>
                    <div class="bg-white/10 p-4 rounded-2xl border border-white/20">
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-1">Identificação CPF</p>
                        <p class="text-white font-mono text-sm tracking-widest">${escapeHtml(data.cpf)}</p>
                    </div>
                </div>
                <div class="bg-white/10 p-4 rounded-2xl border border-white/20">
                    <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-1">Endereço de Email</p>
                    <p class="text-white font-medium">${escapeHtml(data.email)}</p>
                </div>
                ${renderMaterias(data.materias)}
            </div>
        `;
    } else if (type === 'aluno') {
        title = 'DETALHES DO DISCENTE';
        content = `
            <div class="space-y-4">
                <div class="bg-white/10 p-4 rounded-2xl border border-white/20">
                    <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-1">Nome do Aluno</p>
                    <p class="text-lg text-white font-black tracking-tighter">${escapeHtml(data.nome)}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-purple-500/20 p-4 rounded-2xl border border-purple-400/30">
                        <p class="text-[10px] text-purple-200 uppercase font-black tracking-widest mb-1">Registro (RA)</p>
                        <p class="text-white font-black font-mono tracking-wider">${escapeHtml(data.ra)}</p>
                    </div>
                    <div class="bg-white/10 p-4 rounded-2xl border border-white/20">
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-1">CPF</p>
                        <p class="text-white font-mono text-sm uppercase">${escapeHtml(data.cpf)}</p>
                    </div>
                </div>

                <div class="bg-white/10 p-4 rounded-2xl border border-white/20">
                    <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-1">E-mail Acadêmico</p>
                    <p class="text-white/90 font-medium italic">${escapeHtml(data.email)}</p>
                </div>

                ${renderMaterias(data.materias)}
            </div>
        `;
    } else if (type === 'materia') {
        title = 'DETALHES DA DISCIPLINA';
        content = `
            <div class="space-y-4">
                <div class="bg-teal-500/20 p-5 rounded-2xl border border-teal-400/30">
                    <p class="text-[10px] text-teal-200 uppercase font-black tracking-widest mb-1">Nome da Matéria</p>
                    <p class="text-xl text-white font-black tracking-tighter uppercase italic">${escapeHtml(data.nome)}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/10 p-4 rounded-2xl border border-white/20">
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-1">Localização</p>
                        <p class="text-white font-bold"><span class="text-white/40 mr-1">SALA</span> ${escapeHtml(data.sala)}</p>
                    </div>
                    <div class="bg-white/10 p-4 rounded-2xl border border-white/20">
                        <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-1">Carga Horária</p>
                        <p class="text-white font-bold">${escapeHtml(data.carga_horaria)} <span class="text-white/40 text-[10px]">HORAS</span></p>
                    </div>
                </div>
                
                <div>
                    <p class="text-[10px] text-white/50 uppercase font-black tracking-widest mb-2">Disponibilidade por Turno</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 rounded-xl border ${data.horario_matutino ? 'bg-green-500/20 border-green-400/40' : 'bg-white/5 border-white/10 opacity-30'}">
                            <p class="text-[9px] ${data.horario_matutino ? 'text-green-300' : 'text-white/20'} font-black uppercase tracking-widest">Matutino</p>
                            <p class="text-[10px] font-mono text-white mt-1">${escapeHtml(data.horario_matutino || '---')}</p>
                        </div>
                        <div class="p-3 rounded-xl border ${data.horario_vespertino ? 'bg-green-500/20 border-green-400/40' : 'bg-white/5 border-white/10 opacity-30'}">
                            <p class="text-[9px] ${data.horario_vespertino ? 'text-green-300' : 'text-white/20'} font-black uppercase tracking-widest">Vespertino</p>
                            <p class="text-[10px] font-mono text-white mt-1">${escapeHtml(data.horario_vespertino || '---')}</p>
                        </div>
                        <div class="p-3 rounded-xl border ${data.horario_noturno ? 'bg-green-500/20 border-green-400/40' : 'bg-white/5 border-white/10 opacity-30'}">
                            <p class="text-[9px] ${data.horario_noturno ? 'text-green-300' : 'text-white/20'} font-black uppercase tracking-widest">Noturno</p>
                            <p class="text-[10px] font-mono text-white mt-1">${escapeHtml(data.horario_noturno || '---')}</p>
                        </div>
                    </div>
                </div>

                ${renderProfessores(data.professores)}
            </div>
        `;
    }

    modalTitle.innerHTML = title;
    // ... rest of logic remains same
    modalBody.innerHTML = content;
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}

function closeModal() {
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// Close on backdrop click
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});
