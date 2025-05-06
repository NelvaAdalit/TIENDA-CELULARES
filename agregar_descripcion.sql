-- Añadir columna descripcion a la tabla celulares si no existe
ALTER TABLE celulares ADD COLUMN IF NOT EXISTS descripcion TEXT AFTER modelo;

-- Actualizar los registros existentes con descripciones predeterminadas
UPDATE celulares SET descripcion = CONCAT('Smartphone ', marca, ' ', modelo, ' con excelentes características y rendimiento.') WHERE descripcion IS NULL OR descripcion = '';

-- Actualizar descripciones específicas para cada modelo
UPDATE celulares SET descripcion = 'Samsung Galaxy A14 con pantalla HD+ de 6.6", cámara principal de 50MP y gran batería de 5000mAh para uso prolongado.' WHERE id = 1;
UPDATE celulares SET descripcion = 'Samsung Galaxy S23 con procesador Snapdragon 8 Gen 2, pantalla Dynamic AMOLED 2X y sistema de cámaras profesional.' WHERE id = 2;
UPDATE celulares SET descripcion = 'Xiaomi Redmi Note 12 con pantalla AMOLED de 120Hz, procesador Snapdragon 685 y carga rápida de 33W.' WHERE id = 3;
UPDATE celulares SET descripcion = 'Xiaomi Poco X5 Pro con pantalla AMOLED de 120Hz, cámara principal de 108MP y procesador Snapdragon 778G.' WHERE id = 4;
UPDATE celulares SET descripcion = 'Apple iPhone 13 con chip A15 Bionic, pantalla Super Retina XDR y sistema de cámaras dual avanzado.' WHERE id = 5;
UPDATE celulares SET descripcion = 'Apple iPhone SE 2022 con el potente chip A15 Bionic, diseño clásico con Touch ID y resistencia al agua.' WHERE id = 6;
UPDATE celulares SET descripcion = 'Motorola Moto G73 con conectividad 5G, pantalla de 120Hz y cámara principal de 50MP con tecnología Quad Pixel.' WHERE id = 7;
UPDATE celulares SET descripcion = 'Huawei Nova Y70 con enorme batería de 6000mAh, pantalla FullView de 6.75" y cámara triple con IA.' WHERE id = 8;
