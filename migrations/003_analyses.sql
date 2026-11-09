CREATE TABLE analyses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    input_value VARCHAR(100) NOT NULL,
    dashboard_json JSONB NOT NULL,
    public_hash VARCHAR(32) UNIQUE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Индекс для быстрого поиска по хешу
CREATE INDEX idx_analyses_public_hash ON analyses(public_hash);
-- Индекс для истории пользователя
CREATE INDEX idx_analyses_user_id ON analyses(user_id);