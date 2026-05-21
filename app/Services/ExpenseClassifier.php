<?php

namespace App\Services;

/*
|--------------------------------------------------------------------------
| ExpenseClassifier
|--------------------------------------------------------------------------
| Clasifica gastos según Art. 29 (deducibles), Art. 29-A (no deducibles)
| y Art. 30 (activos depreciables) de la LISR de El Salvador.
|
| REGLA CLAVE (Art. 29 LISR):
|   Un gasto es deducible si es NECESARIO para producir ingresos
|   y está debidamente documentado.
|
| NOTA IMPORTANTE:
|   Harina, azúcar, ingredientes → DEDUCIBLES para panaderías/pastelerías
|   (son materia prima del negocio, no consumo personal)
|   Solo son NO DEDUCIBLES si son para consumo personal del dueño.
|
| Fuentes:
|   https://centr4l.com/project/gastos-deducibles-no-deducibles-segun-la-ley-el-salvador/
|   https://transparencia.mh.gob.sv/downloads/pdf/DC5101_19_Ley_de_Impuesto_sobre_la_Renta.pdf
*/

class ExpenseClassifier
{
    // ─── NO DEDUCIBLES primero (Art. 29-A LISR) ───────────────────────────
    // Se evalúan ANTES que los deducibles para evitar falsos positivos.
    // Solo gastos PERSONALES o sin relación con el negocio.
    private static array $noDeducibles = [
        // Consumo personal de alimentos (NO materia prima de negocio)
        'comida personal', 'almuerzo personal', 'desayuno personal', 'cena personal',
        'supermercado personal', 'despensa personal', 'mercado personal',
        'víveres personales', 'groceries',

        // Ropa y cuidado personal (Art. 29-A)
        'ropa', 'ropa casual', 'ropa personal', 'zapatos personales', 'calzado personal',
        'tenis personal', 'vestimenta', 'perfume', 'cosmético', 'maquillaje personal',
        'salon de belleza', 'barbería personal',

        // Entretenimiento personal
        'netflix', 'spotify', 'disney', 'disney+', 'hbo', 'apple tv', 'amazon prime',
        'youtube premium', 'videojuego', 'juego personal', 'cine personal',
        'teatro personal', 'concierto personal',

        // Salud y vida personal
        'médico personal', 'medico personal', 'farmacia personal', 'medicina personal',
        'gym personal', 'gimnasio personal', 'membresía gym', 'seguro de vida personal',

        // Hogar personal
        'luz de casa', 'agua de casa', 'internet de casa', 'alquiler de casa',
        'renta de casa', 'hipoteca', 'mascotas', 'pet', 'cuota casa',

        // Sanciones (Art. 29-A inciso final)
        'multa', 'recargo tributario', 'mora hacienda', 'sancion fiscal',
        'interés moratorio',

        // Dividendos y retiros personales
        'dividendo', 'retiro personal', 'préstamo personal',

        // Bebidas alcohólicas y tabaco fuera del giro
        'alcohol personal', 'cerveza personal', 'licor personal', 'cigarrillo personal',

        // Joyería y lujo personal
        'joyería', 'reloj personal', 'accesorio personal',
    ];

    // ─── ACTIVOS DEPRECIABLES (Art. 30 y 30-A LISR) ───────────────────────
    // Bienes que duran más de 1 año — se deducen gradualmente.
    private static array $activos = [
        // Equipo de cómputo (vida útil 2 años — 50% anual)
        'laptop', 'computadora', 'computador', 'pc', 'mac', 'imac', 'macbook',
        'ipad', 'tablet', 'monitor', 'teclado mecánico', 'mouse', 'impresora',
        'escaner', 'escáner', 'router', 'disco duro externo', 'disco externo',
        'disco ssd', 'memoria ram', 'ups', 'switch de red',

        // Equipo audiovisual y creativo
        'cámara fotográfica', 'cámara de video', 'drone', 'lente fotográfico',
        'tripode', 'trípode', 'micrófono', 'ring light', 'luz de estudio',
        'panel de luz', 'audio interface', 'bocina estudio', 'monitor de audio',
        'mezcladora', 'controlador midi',

        // Equipo de pastelería y restaurante (vida útil según tabla)
        'horno industrial', 'horno de panadería', 'batidora industrial',
        'batidora planetary', 'amasadora', 'laminadora', 'refrigerador comercial',
        'congelador comercial', 'vitrina refrigerada', 'vitrina de exhibición',
        'máquina de café', 'cafetera industrial', 'freidora', 'plancha industrial',
        'cocina industrial', 'campana extractora', 'licuadora industrial',
        'procesador de alimentos',

        // Mobiliario y equipo de oficina/local
        'escritorio', 'silla ergonómica', 'silla de oficina', 'mesa de trabajo',
        'estante', 'estantería', 'anaquel', 'archivero', 'locker', 'mueble oficina',
        'mostrador', 'vitrina de madera', 'mueble local',

        // Vehículos (Art. 30 — 25% anual)
        'vehículo', 'vehiculo', 'carro', 'auto', 'automóvil', 'motocicleta',
        'moto', 'camioneta', 'pickup', 'furgoneta', 'bicicleta eléctrica',

        // Maquinaria y herramientas duraderas
        'maquinaria', 'máquina de coser', 'compresor', 'soldadora', 'taladro',
        'cortadora', 'sierra', 'generador',

        // Intangibles amortizables (Art. 30-A)
        'licencia de software', 'software adquirido', 'patente', 'marca registrada',
        'franquicia', 'dominio web comprado', 'aplicación propia', 'sistema propio',
    ];

    // ─── DEDUCIBLES (Art. 29 LISR) ────────────────────────────────────────
    // Gastos necesarios y propios del negocio, debidamente documentados.
    private static array $deducibles = [
        // ── Materia prima e insumos de producción ──
        // Para pastelerías, panaderías, restaurantes y manufactura
        'harina', 'azúcar', 'mantequilla', 'huevo', 'huevos', 'leche', 'crema',
        'cacao', 'chocolate', 'vainilla', 'levadura', 'sal negocio', 'aceite negocio',
        'colorante alimenticio', 'saborizante', 'gelatina', 'betún', 'fondant',
        'mermelada', 'relleno pastelería', 'fruta negocio', 'nuez', 'almendra',
        'ingrediente', 'materia prima', 'insumo producción',

        // ── Empaque y logística ──
        'empaque', 'embalaje', 'caja de envío', 'caja de cartón', 'bolsa kraft',
        'bolsa biodegradable', 'papel de empaque', 'burbuja', 'papel burbuja',
        'etiqueta', 'sticker', 'cinta de embalaje', 'caja pastelería',
        'envase', 'contenedor', 'bandeja', 'servilleta negocio',

        // ── Servicios digitales y tecnología ──
        'adobe', 'figma', 'canva', 'notion', 'slack', 'zoom', 'teams',
        'google workspace', 'gsuite', 'microsoft 365', 'office 365',
        'dropbox', 'github', 'gitlab', 'jira', 'asana', 'trello', 'monday',
        'hubspot', 'mailchimp', 'sendinblue', 'zapier', 'make', 'n8n',
        'shopify', 'woocommerce', 'wix', 'webflow', 'wordpress',
        'chatgpt', 'claude', 'openai', 'midjourney',

        // ── Hosting y servicios web ──
        'hosting', 'servidor', 'vps', 'server', 'dominio', 'cloudflare',
        'aws', 'google cloud', 'azure', 'digitalocean', 'vercel', 'heroku',
        'namecheap', 'godaddy', 'siteground',

        // ── Telecomunicaciones ──
        'internet', 'wifi', 'claro', 'tigo', 'digicel', 'fibra óptica',
        'banda ancha', 'teléfono negocio', 'celular negocio', 'plan telefónico negocio',
        'llamadas negocio', 'whatsapp business',

        // ── Combustible y transporte de negocio (Art. 29 #3) ──
        'combustible', 'gasolina', 'diesel', 'gas vehículo',
        'uber negocio', 'uber reunión', 'didi negocio', 'taxi negocio',
        'transporte negocio', 'pasaje negocio', 'flete', 'envío',
        'courier', 'dhl', 'fedex', 'correos', 'mensajería',
        'delivery negocio',

        // ── Energía y servicios del local ──
        'gas propano', 'gas industrial', 'gas licuado', 'propano',
        'electricidad local', 'luz local', 'luz taller', 'luz oficina',
        'agua local', 'agua taller', 'agua oficina',
        'alquiler local', 'alquiler oficina', 'alquiler bodega', 'renta local',
        'coworking', 'espacio de trabajo',

        // ── Mantenimiento y reparaciones ──
        'mantenimiento', 'reparación', 'servicio técnico', 'mantencion',
        'calibración', 'limpieza local', 'aseo local',

        // ── Seguros del negocio ──
        'seguro negocio', 'seguro de equipo', 'seguro de local',
        'póliza empresarial', 'prima de seguro negocio',

        // ── Publicidad y marketing ──
        'publicidad', 'anuncio', 'facebook ads', 'google ads', 'instagram ads',
        'tiktok ads', 'marketing', 'promoción', 'pauta', 'campaña digital',
        'diseño publicitario', 'flyer', 'banner publicitario', 'branding',
        'fotografía producto', 'video marketing',

        // ── Servicios profesionales ──
        'contador', 'contabilidad', 'abogado', 'notario', 'consultoría',
        'asesoría legal', 'asesoría contable', 'diseñador', 'desarrollador web',
        'community manager', 'fotógrafo producto', 'subcontrato', 'honorarios',

        // ── Papelería y suministros de oficina ──
        'papelería', 'papel', 'tinta', 'cartucho', 'tóner', 'lapicero',
        'cuaderno negocio', 'folder', 'archivador', 'engrapadora', 'sello',
        'facturación', 'factura', 'comprobante', 'recibo',

        // ── Capacitación vinculada al negocio (Art. 29 #14) ──
        'curso negocio', 'capacitación', 'certificación profesional', 'taller negocio',
        'seminario', 'conferencia negocio', 'diplomado', 'udemy', 'platzi',
        'coursera', 'linkedin learning', 'formación profesional',

        // ── Viáticos de negocio (Art. 29 #3) ──
        'viático', 'hospedaje negocio', 'hotel negocio', 'vuelo negocio',
        'pasaje negocio', 'alojamiento negocio', 'airbnb negocio',

        // ── Comisiones bancarias ──
        'comisión bancaria', 'cuota banco', 'tarjeta empresarial',
        'stripe', 'paypal business', 'pasarela de pago', 'transferencia bancaria',

        // ── Salarios y prestaciones ──
        'salario empleado', 'planilla', 'aguinaldo', 'prestaciones laborales',
        'isss', 'afp', 'seguro social', 'vacaciones empleado',

        // ── Equipo de gas y energía ──
        'gas propano', 'gas licuado', 'propano negocio', 'gas negocio',
        // ── Otros insumos de operación ──
        'detergente local', 'cloro local', 'guantes trabajo', 'mascarilla trabajo',
        'uniforme trabajo', 'gorra trabajo', 'delantal trabajo',
    ];

    // ─── API pública ───────────────────────────────────────────────────────

    public static function clasificar(array $gastos): array
    {
        return array_map(fn($g) => self::clasificarUno($g), $gastos);
    }

    private static function clasificarUno(string $gasto): array
    {
        $n = strtolower(trim($gasto));

        // Si el gasto incluye "negocio", "empresa", "local" o "trabajo"
        // es un contexto de negocio — va directo a deducibles/activos
        $esContextoNegocio = str_contains($n, 'negocio') || str_contains($n, 'empresa')
                          || str_contains($n, 'local')   || str_contains($n, 'trabajo')
                          || str_contains($n, 'taller')  || str_contains($n, 'industrial');

        if ($esContextoNegocio) {
            foreach (self::$activos as $p) {
                if (str_contains($n, $p)) return self::respuesta($gasto, 'activo');
            }
            foreach (self::$deducibles as $p) {
                if (str_contains($n, $p)) return self::respuesta($gasto, 'deducible');
            }
            // Si tiene contexto de negocio pero no matchea, es deducible por defecto
            return self::respuesta($gasto, 'deducible');
        }

        // Sin contexto: no deducibles → activos → deducibles → revisar
        foreach (self::$noDeducibles as $p) {
            if (str_contains($n, $p)) return self::respuesta($gasto, 'no_deducible');
        }
        foreach (self::$activos as $p) {
            if (str_contains($n, $p)) return self::respuesta($gasto, 'activo');
        }
        foreach (self::$deducibles as $p) {
            if (str_contains($n, $p)) return self::respuesta($gasto, 'deducible');
        }

        return self::respuesta($gasto, 'revisar');
    }

    private static function respuesta(string $gasto, string $tipo): array
    {
        $config = match($tipo) {
            'deducible' => [
                'etiqueta'    => 'Deducible',
                'descripcion' => 'Reduce tu renta imponible — pagas menos ISR (Art. 29 LISR)',
                'consejo'     => 'Guarda la factura o CCF como respaldo.',
                'base_legal'  => 'Art. 29 LISR',
                'color'       => 'green',
            ],
            'no_deducible' => [
                'etiqueta'    => 'No deducible',
                'descripcion' => 'Gasto personal — no reduce tu carga fiscal (Art. 29-A LISR)',
                'consejo'     => 'No incluyas este gasto en tu declaración de renta.',
                'base_legal'  => 'Art. 29-A LISR',
                'color'       => 'red',
            ],
            'activo' => [
                'etiqueta'    => 'Activo depreciable',
                'descripcion' => 'Bien duradero — se deduce gradualmente año a año (Art. 30 LISR)',
                'consejo'     => 'Regístralo como activo fijo y deprecíalo con tu contador.',
                'base_legal'  => 'Art. 30 LISR',
                'color'       => 'blue',
            ],
            'revisar' => [
                'etiqueta'    => 'Consultar contador',
                'descripcion' => 'No pudimos clasificarlo automáticamente',
                'consejo'     => 'Depende del uso — si es para el negocio puede ser deducible.',
                'base_legal'  => 'Art. 29 LISR',
                'color'       => 'amber',
            ],
        };

        return array_merge(['gasto' => $gasto, 'tipo' => $tipo], $config);
    }
}