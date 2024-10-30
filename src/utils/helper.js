
const {restApiUrl,nonce,ajaxUrl,translate_array,postUrl } = hexCuponData;

export function getPostRequestUrl (action){
	return `${postUrl}?action=${action}`;
}
 export function getNonce (){
	return nonce;
}

