# Redirecionando IPv4 para IPv6 com `socat`

Este guia explica como usar o `socat` para acessar um serviço (como o PostgreSQL do Supabase) que **só responde por IPv6**, criando um túnel local IPv4 → IPv6.

---

## 🧨 Problema

Alguns serviços modernos (como Supabase) fornecem apenas endereços IPv6. Porém, seu sistema (como o macOS) ou rede pode não conseguir acessar diretamente um IP IPv6, resultando no erro:

> `connection to server at "::ipv6..." failed: Network is unreachable`

---

## ✅ Solução

Criar um redirecionamento local com `socat`, escutando via IPv4 e repassando a conexão para o destino IPv6 remoto.

---

## 🔧 Pré-requisitos

- Ter o `socat` instalado:

  ```bash
  brew install socat
  ```

- Obter o IPv6 do seu banco (exemplo com Supabase):

  ```bash
  dig AAAA db.xxxxxxxxx.supabase.co
  ```

---

## 🚀 Comando

```bash
socat TCP4-LISTEN:5433,fork TCP6:[<ENDEREÇO_IPV6>]:5432
```

**Explicando:**
- `5433`: porta local para escutar (pode ser qualquer uma livre).
- `[<ENDEREÇO_IPV6>]`: o IP v6 do destino (sempre entre colchetes).
- `5432`: porta padrão do PostgreSQL no destino.

**Exemplo real:**

```bash
socat TCP4-LISTEN:5433,fork TCP6:[2600:1f16:1cd0:3320:a909:6325:4707:a81c]:5432
```

---

## 🧪 Testando

Configure seu app para conectar em:

- **Host:** `localhost`
- **Porta:** `5433`

O `socat` vai redirecionar para o Supabase via IPv6 por trás dos panos.

---

## 💡 Dica extra

Se quiser manter o socat rodando em background:

```bash
nohup socat TCP4-LISTEN:5433,fork TCP6:[...]:5432 &
```