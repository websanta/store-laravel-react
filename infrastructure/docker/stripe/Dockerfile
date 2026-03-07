FROM stripe/stripe-cli:latest

# Устанавливаем curl для отправки вебхуков
RUN apk add --no-cache curl

# Дополнительно можно установить другие полезные утилиты
RUN apk add --no-cache jq

# Переопределяем entrypoint, чтобы сохранить оригинальное поведение
ENTRYPOINT ["stripe"]
CMD ["--help"]
