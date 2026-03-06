CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'editor', 'author') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    image_url VARCHAR(255),
    summary TEXT,
    content LONGTEXT,
    category_id INT,
    views INT DEFAULT 0,
    ai_generated TINYINT(1) DEFAULT 0,
    seo_title VARCHAR(255),
    seo_description TEXT,
    seo_tags VARCHAR(255),
    status ENUM('published', 'draft', 'archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam') DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

-- Insert Default Categories
INSERT IGNORE INTO categories (name, slug) VALUES 
('Mundo', 'mundo'),
('Economia', 'economia'),
('Tecnologia', 'tecnologia'),
('Criptomoedas', 'criptomoedas'),
('Brasil', 'brasil'),
('Entretenimento', 'entretenimento');

-- Default Settings
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('site_name', 'Automated News Portal'),
('site_description', 'Últimas notícias automatizadas e em tempo real.'),
('site_logo', ''),
('site_favicon', ''),
('adsense_header', ''),
('adsense_article_mid', ''),
('adsense_sidebar', ''),
('adsense_footer', ''),
('analytics_id', '');
