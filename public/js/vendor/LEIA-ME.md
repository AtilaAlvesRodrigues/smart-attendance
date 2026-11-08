# Bibliotecas de terceiros

Cópias **sem alteração** dos arquivos publicados na cdnjs, servidas pelo próprio site para que a Content-Security-Policy não precise liberar nenhuma CDN (veja `docs/falhas-de-seguranca.md`, SEC-04).

| Arquivo | Biblioteca | Versão | Licença | SHA-512 (igual ao publicado pela cdnjs) |
|---|---|---|---|---|
| `qrcode.min.js` | qrcodejs | 1.0.0 | MIT | `CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==` |
| `jspdf.umd.min.js` | jsPDF | 2.5.1 | MIT | `qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==` |
| `jspdf.plugin.autotable.min.js` | jsPDF-AutoTable | 3.5.28 | MIT | `03CCNkeosDFN2zCCu4vLpu3pJfZcrL48F3yB8k87ejT+OVMwco7IH3FW02vtbGhdncS6gyYZ/duYaC/K62xQPQ==` |

O teste `VendorIntegrityTest` falha se algum arquivo for alterado. Para atualizar uma biblioteca: baixe a nova versão da cdnjs, confira o hash em `https://api.cdnjs.com/libraries/<nome>/<versão>?fields=sri` e atualize esta tabela e o teste.
