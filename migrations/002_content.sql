-- Персонажи для сравнительной аналитики
CREATE TABLE characters (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Шаблоны сравнений (с плейсхолдерами {name}, {input} и т.д.)
CREATE TABLE compare_templates (
    id SERIAL PRIMARY KEY,
    template TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Действия (actions) для шаблонов
CREATE TABLE actions (
    id SERIAL PRIMARY KEY,
    text VARCHAR(500) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Предметы (items) для шаблонов
CREATE TABLE items (
    id SERIAL PRIMARY KEY,
    text VARCHAR(500) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Комментарии (comments) для шаблонов
CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    text VARCHAR(500) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Реакции (reactions) для шаблонов
CREATE TABLE reactions (
    id SERIAL PRIMARY KEY,
    text VARCHAR(500) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Предсказания (365 штук, по одному на день)
CREATE TABLE predictions (
    id SERIAL PRIMARY KEY,
    text TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Имена для параллельной вселенной
CREATE TABLE secret_names (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Титулы
CREATE TABLE secret_titles (
    id SERIAL PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Уровни доступа
CREATE TABLE secret_levels (
    id SERIAL PRIMARY KEY,
    level VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Категории для круговой диаграммы
CREATE TABLE chart_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);