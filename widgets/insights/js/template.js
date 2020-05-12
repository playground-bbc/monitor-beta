const baseUrlApi = `${origin}/monitor-beta/web/monitor/api/insights/`;
const baseUrlImg = `${origin}/monitor-beta/web/img/`;


const titleInsights = {
	//facebook
	'page_impressions': 'Impresiones totales diarias',
	'page_post_engagements': 'Alcance total diario',
	'page_impressions_unique': 'Engagements Post Diarios',
	'fan_count': 'El número de usuarios a los que les gusta la página',
	// instagram
	'reach': 'Alcance',
	'impressions': 'Impresiones',
	'profile_views': 'Visitas al perfil',
	'follower_count': 'Número seguidores',
	'followers_count': 'Seguidores Unicos',
};

const headersPost = {
	//facebook
	'post_impressions':'Impresiones totales de toda la vida',
	'post_engaged_users':'Usuarios comprometidos de por vida',
	'post_reactions_by_type_total': 'Likes / ROT',
	// instagram
	'impressions' : 'Impresiones',
	'reach' : 'Alcance',
	'engagement' : 'Interacción',
	'likes' : 'Me Gusta',
	'coments': 'Comentarios y respuestas',
};


const titleToolTipsInsights = {
	//facebook
	'page_impressions': 'El número total de impresiones vistas de cualquier contenido asociado con su página. (Diario)',
	'page_impressions_unique': 'El número de personas que han visto cualquier contenido asociado con su página.(Diario)',
	'page_post_engagements': 'La cantidad de veces que las personas se han involucrado con tus publicaciones a través de Me gusta, comentarios y recursos compartidos y más (Diario)',
	'fan_count': 'El número de usuarios a los que les gusta la página. Para las páginas globales, este es el recuento de todas las páginas de la marca. (Lifetime)',
	// instagram
	'reach': 'Número total de cuentas únicas que han visto este perfil dentro del período especificado (Diario)',
	'impressions': 'Número total de veces que se ha visto este perfil dentro del período especificado (Diario)',
	'profile_views': 'Número total de cuentas únicas que han visto este perfil dentro del período especificado (Diario)',
	'follower_count': 'Número total de seguidores nuevos cada día dentro del rango especificado (Diario)',
	'followers_count': 'Número total de cuentas únicas que siguen este perfil (Lifetime)',
};

const titleInsightsTableTooltips = {
	// facebook
	'post_impressions' : 'Número de veces que se mostró en la pantalla de una persona la publicación de tu página. Las publicaciones incluyen estados, fotos, enlaces, videos y más.',
	'post_engaged_users' : 'Número de personas que hicieron clic en cualquier lugar de tus publicaciones.',
	'post_reactions_by_type_total' : 'Número total de reacciones a la publicación por tipo.',
	// Instagram
	'impressions' : 'Número total de veces que se vio el objeto multimedia',
	'reach' : 'Número total de cuentas únicas que vieron el objeto multimedia',
	'engagement' : 'Número total de Me gusta y comentarios en el objeto multimedia',
	'likes' : 'Numero de Likes del Post',
	'coments': 'Numero de Comentarios y respuestas del Post',
}