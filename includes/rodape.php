<?php if (isset($_SESSION['user_id'])) : ?>
  <footer class="mt-auto py-3 border-top bg-light">
      <div class="container d-flex flex-wrap justify-content-between align-items-center">
          <p class="col-md-4 mb-0 text-muted small">&copy; <?= date('Y') ?> Gerenciador de Estoque</p>

          <ul class="nav col-md-4 justify-content-end list-unstyled d-flex small">
              <li class="ms-3"><a class="text-muted text-decoration-none" href="/gerenciador_de_estoque/developers.php"><i class="fas fa-code me-1"></i>Developers API</a></li>
              <li class="ms-3"><a class="text-muted text-decoration-none" href="#">Ajuda</a></li>
              <li class="ms-3"><a class="text-muted text-decoration-none" href="#">Sobre</a></li>
          </ul>
      </div>
  </footer>
  </div>
</main>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/gerenciador_de_estoque/assets/js/admin.js"></script>
<script src="/gerenciador_de_estoque/assets/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('globalSearchInput');
    const searchResults = document.getElementById('globalSearchResults');
    
    if (!searchInput || !searchResults) return;

    let debounceTimer;

    const performSearch = () => {
        const query = searchInput.value;

        if (query.length < 2) {
            searchResults.innerHTML = '';
            searchResults.style.display = 'none';
            return;
        }

        fetch(`/gerenciador_de_estoque/api/busca_global.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                if (Object.keys(data).length > 0) {
                    for (const category in data) {
                        const categoryHeader = document.createElement('li');
                        categoryHeader.className = 'list-group-item search-result-category';
                        categoryHeader.textContent = category;
                        searchResults.appendChild(categoryHeader);

                        data[category].forEach(item => {
                            const listItem = document.createElement('a');
                            listItem.className = 'list-group-item list-group-item-action';
                            listItem.href = item.url;
                            listItem.textContent = item.name;
                            searchResults.appendChild(listItem);
                        });
                    }
                    searchResults.style.display = 'block';
                } else {
                    const noResults = document.createElement('li');
                    noResults.className = 'list-group-item text-muted';
                    noResults.textContent = 'Nenhum resultado encontrado.';
                    searchResults.appendChild(noResults);
                    searchResults.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Erro na busca global:', error);
                searchResults.style.display = 'none';
            });
    };

    searchInput.addEventListener('keyup', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 300); // 300ms delay
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    searchInput.addEventListener('focus', performSearch);

    // Mobile Search Logic
    const mobileTrigger = document.getElementById('mobile-search-trigger');
    const mobileOverlay = document.getElementById('mobile-search-overlay');
    const closeMobileSearch = document.getElementById('close-mobile-search');
    const mobileInput = document.getElementById('mobileSearchInput');
    const mobileResults = document.getElementById('mobileSearchResults');

    if (mobileTrigger && mobileOverlay && mobileInput) {
        mobileTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            mobileOverlay.classList.remove('d-none');
            mobileOverlay.classList.add('d-flex');
            setTimeout(() => mobileInput.focus(), 100);
        });

        const closeSearch = () => {
            mobileOverlay.classList.remove('d-flex');
            mobileOverlay.classList.add('d-none');
            mobileInput.value = '';
            if(mobileResults) mobileResults.innerHTML = '';
        };

        closeMobileSearch.addEventListener('click', closeSearch);
        
        // Close on Esc
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !mobileOverlay.classList.contains('d-none')) {
                closeSearch();
            }
        });

        // Use same search logic for mobile
        if(mobileInput) {
            mobileInput.addEventListener('keyup', () => {
                const query = mobileInput.value;
                // Re-use logic (simplified duplication for safety)
                 if (query.length < 2) {
                    if(mobileResults) {
                        mobileResults.innerHTML = '';
                        mobileResults.style.display = 'none';
                    }
                    return;
                }
                
                // Using same endpoint
                fetch(`/gerenciador_de_estoque/api/busca_global.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if(mobileResults) {
                            mobileResults.innerHTML = '';
                            if (Object.keys(data).length > 0) {
                                for (const category in data) {
                                    const categoryHeader = document.createElement('li');
                                    categoryHeader.className = 'list-group-item search-result-category';
                                    categoryHeader.textContent = category;
                                    mobileResults.appendChild(categoryHeader);
            
                                    data[category].forEach(item => {
                                        const listItem = document.createElement('a');
                                        listItem.className = 'list-group-item list-group-item-action';
                                        listItem.href = item.url;
                                        listItem.textContent = item.name;
                                        mobileResults.appendChild(listItem);
                                    });
                                }
                                mobileResults.style.display = 'block';
                            } else {
                                const noResults = document.createElement('li');
                                noResults.className = 'list-group-item text-muted';
                                noResults.textContent = 'Nenhum resultado encontrado.';
                                mobileResults.appendChild(noResults);
                                mobileResults.style.display = 'block';
                            }
                        }
                    })
                    .catch(console.error);
            });
        }
    }
});
</script>
<!-- AI Agent Floating Button -->
<div id="ai-agent-fab" style="position: fixed; bottom: 30px; right: 30px; z-index: 1050; cursor: pointer;">
    <div class="bg-white rounded-circle shadow-lg d-flex align-items-center justify-content-center p-1" style="width: 60px; height: 60px; transition: transform 0.2s;">
        <img src="/gerenciador_de_estoque/assets/img/ai_agent_logo.png" alt="AI Agent" style="width: 32px; height: 32px;">
    </div>
</div>

<!-- AI Agent Offcanvas Interface -->
<div class="offcanvas offcanvas-end shadow-lg border-0 rounded-start-4" data-bs-scroll="true" tabindex="-1" id="offcanvasAIAgent" aria-labelledby="offcanvasAIAgentLabel" style="width: 400px;">
    <div class="offcanvas-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-2">
            <img src="/gerenciador_de_estoque/assets/img/ai_agent_logo.png" alt="AI" width="24">
            <h5 class="offcanvas-title fw-bold text-gradient" id="offcanvasAIAgentLabel" style="background: -webkit-linear-gradient(45deg, #4285F4, #9B72CB); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Assistente IA</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    
    <!-- Agent Selector -->
    <div class="px-3 py-2 bg-light border-bottom">
        <select id="agent-selector" class="form-select form-select-sm border-0 bg-transparent text-muted fw-bold">
            <option value="">Selecione um Agente...</option>
            <!-- Populated via JS -->
        </select>
        <select id="model-selector" class="form-select form-select-sm border-0 bg-transparent text-primary fw-bold mt-1">
            <optgroup label="Google Gemini">
                <option value="gemini-2.5-flash" selected>Gemini 2.5 Flash (Latest)</option>
                <option value="gemini-2.5-pro">Gemini 2.5 Pro (Best)</option>
                <option value="gemini-2.0-flash">Gemini 2.0 Flash</option>
            </optgroup>
            <optgroup label="OpenAI">
                <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                <option value="gpt-4o">GPT-4o</option>
            </optgroup>
        </select>
    </div>

    <div class="offcanvas-body p-0 d-flex flex-column bg-light" style="height: 100%;">
        <!-- Chat Area -->
        <div id="chat-messages" class="flex-grow-1 p-3" style="overflow-y: auto;">
            <div class="text-center text-muted mt-5">
                <i class="fas fa-magic fa-2x mb-3 text-secondary opacity-25"></i>
                <p class="small">Escolha um agente acima e comece a conversa.</p>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white border-top">
            <div class="input-group bg-light rounded-pill border overflow-hidden p-1 shadow-sm">
                <input type="text" id="chat-input" class="form-control border-0 bg-transparent ps-3" placeholder="Pergunte algo..." aria-label="Mensagem">
                <button class="btn btn-primary rounded-pill px-4" type="button" id="btn-send-chat">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div id="typing-indicator" class="text-muted small ms-3 mt-2" style="display: none;">
                <span class="spinner-grow spinner-grow-sm text-primary" role="status" aria-hidden="true"></span>
                Pensando...
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fab = document.getElementById('ai-agent-fab');
    // Ensure Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JS is not loaded or failed to load.');
        return;
    }
    
    // Check if elements exist before using them
    const offcanvasEl = document.getElementById('offcanvasAIAgent');
    if (!offcanvasEl || !fab) return;

    const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('btn-send-chat');
    const messagesContainer = document.getElementById('chat-messages');
    const agentSelector = document.getElementById('agent-selector');
    const typingIndicator = document.getElementById('typing-indicator');

    if (!chatInput || !sendBtn || !messagesContainer || !agentSelector) return;

    let history = [];

    // Toggle Chat
    fab.addEventListener('click', () => bsOffcanvas.toggle());

    // Load Agents
    function loadAgents() {
        fetch('/gerenciador_de_estoque/api/get_agents_list.php')
            .then(r => r.json())
            .then(data => {
                if(data.length > 0) {
                    agentSelector.innerHTML = data.map(a => `<option value="${a.id}">${a.name} (${a.role})</option>`).join('');
                    // Select first active agent if none selected
                    if (!agentSelector.value) {
                         const active = data.find(a => a.status === 'active');
                         if(active) agentSelector.value = active.id;
                    }
                } else {
                    agentSelector.innerHTML = '<option>Sem agentes criados</option>';
                }
            })
            .catch(e => console.error("Erro ao carregar agentes", e));
    }
    loadAgents();

    // Expose Global Function to Open Chat with specific Agent
    window.openAgentChat = function(agentId) {
        bsOffcanvas.show();
        loadAgents(); // Reload to ensure list is fresh
        // Wait small delay for options to populate if needed, or set directly
        setTimeout(() => {
            if(agentId) agentSelector.value = agentId;
        }, 500);
    };

    // Send Message
    async function sendMessage() {
        const text = chatInput.value.trim();
        const agentId = agentSelector.value;
        const modelId = document.getElementById('model-selector').value;
        
        if (!text || !agentId) return;

        // UI User Message
        appendMessage('user', text);
        chatInput.value = '';
        typingIndicator.style.display = 'block';

        try {
            const res = await fetch('/gerenciador_de_estoque/api/chat_agent.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    agent_id: agentId,
                    message: text,
                    model: modelId,
                    history: history
                })
            });
            
            const data = await res.json();
            
            typingIndicator.style.display = 'none';
            
            if (data.error) {
                appendMessage('system', 'Erro: ' + data.error);
            } else {
                // Split response by delimiter and show bubbles sequentially
                const parts = data.response.split('<<<SPLIT>>>');
                
                // Function to show parts with delay
                const showParts = async () => {
                    for (const part of parts) {
                        if(part.trim()) {
                            appendMessage('model', part.trim());
                            await new Promise(r => setTimeout(r, 800)); // Small delay for natural feel
                        }
                    }
                    if (data.widget) {
                        renderWidget(data.widget);
                    }
                };
                showParts();

                history.push({role: 'user', content: text});
                history.push({role: 'model', content: data.response.replace(/<<<SPLIT>>>/g, '\n\n')}); // Store clean history
            }

        } catch (err) {
            typingIndicator.style.display = 'none';
            appendMessage('system', 'Erro de conexão.');
        }
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') sendMessage(); });

    function appendMessage(role, text) {
        const div = document.createElement('div');
        div.className = `d-flex mb-3 ${role === 'user' ? 'justify-content-end' : 'justify-content-start'}`;
        
        const bubble = document.createElement('div');
        bubble.className = `p-3 rounded-4 shadow-sm ${role === 'user' ? 'bg-primary text-white rounded-bottom-right-0' : 'bg-white text-dark border rounded-bottom-left-0'}`;
        bubble.style.maxWidth = '85%';
        
        // Render Markdown if available, else text
        if (typeof marked !== 'undefined' && role !== 'user') {
             bubble.innerHTML = marked.parse(text);
        } else {
             bubble.innerHTML = text.replace(/\n/g, '<br>');
        }
        
        div.appendChild(bubble);
        messagesContainer.appendChild(div);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function renderWidget(widget) {
        const div = document.createElement('div');
        div.className = 'mb-3 card border-0 shadow-sm overflow-hidden';
        
        let content = `<div class="card-header bg-white fw-bold small text-uppercase py-2">${widget.title || 'Dados'}</div>`;
        content += `<div class="card-body p-0">`;

        if (widget.type === 'table') {
            content += `<div class="table-responsive"><table class="table table-sm table-striped mb-0 small"><thead><tr>`;
            if (widget.data.length > 0) {
                Object.keys(widget.data[0]).forEach(k => content += `<th>${k}</th>`);
                content += `</tr></thead><tbody>`;
                widget.data.forEach(row => {
                    content += `<tr>`;
                    Object.values(row).forEach(v => content += `<td>${v}</td>`);
                    content += `</tr>`;
                });
                content += `</tbody></table></div>`;
            }
        } else if (widget.type === 'card' || widget.type === 'list') {
            if (Array.isArray(widget.data)) {
                 content += '<ul class="list-group list-group-flush">';
                 widget.data.forEach(item => {
                     const txt = item.name ? item.name : JSON.stringify(item);
                     content += `<li class="list-group-item small">${txt}</li>`;
                 });
                 content += '</ul>';
            } else {
                // Single object summary
                content += `<div class="p-3">${widget.text || JSON.stringify(widget.data)}</div>`;
            }
        }

        content += `</div>`;
        div.innerHTML = content;
        messagesContainer.appendChild(div);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>
</body>
</html>