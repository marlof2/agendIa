#!/bin/bash

# Script para iniciar o frontend (yarn dev) e backend (php artisan serve) do AgendIA
# em processos separados

# Cores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   Iniciando AgendIA Development${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Função para limpar processos ao sair
cleanup() {
    echo ""
    echo -e "${RED}Encerrando servidores...${NC}"
    kill $FRONTEND_PID $BACKEND_PID 2>/dev/null
    exit 0
}

# Captura sinais de interrupção
trap cleanup SIGINT SIGTERM

# Inicia o frontend (Vue + Vite)
echo -e "${BLUE}[FRONTEND]${NC} Iniciando yarn dev..."
cd /home/marlo/dev/agendIA/frontend && yarn dev > /tmp/agendia-frontend.log 2>&1 &
FRONTEND_PID=$!
echo -e "${GREEN}[FRONTEND]${NC} Processo iniciado (PID: $FRONTEND_PID)"
echo -e "${GREEN}[FRONTEND]${NC} Logs em: /tmp/agendia-frontend.log"
echo ""

# Aguarda 2 segundos
sleep 2

# Inicia o backend (Laravel)
echo -e "${BLUE}[BACKEND]${NC} Iniciando php artisan serve..."
cd /home/marlo/dev/agendIA/api && php artisan serve --port=8090 > /tmp/agendia-backend.log 2>&1 &
BACKEND_PID=$!
echo -e "${GREEN}[BACKEND]${NC} Processo iniciado (PID: $BACKEND_PID)"
echo -e "${GREEN}[BACKEND]${NC} Logs em: /tmp/agendia-backend.log"
echo ""

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Servidores rodando!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "Frontend: Verifique a porta no log (geralmente http://localhost:5173)"
echo -e "Backend:  http://localhost:8090"
echo ""
echo -e "${BLUE}Para ver os logs em tempo real:${NC}"
echo -e "  Frontend: tail -f /tmp/agendia-frontend.log"
echo -e "  Backend:  tail -f /tmp/agendia-backend.log"
echo ""
echo -e "${RED}Pressione Ctrl+C para encerrar ambos os servidores${NC}"
echo ""

# Mantém o script rodando e mostra logs mesclados
tail -f /tmp/agendia-frontend.log /tmp/agendia-backend.log &
TAIL_PID=$!

# Aguarda até que um dos processos termine
wait $FRONTEND_PID $BACKEND_PID

# Limpa ao final
cleanup

