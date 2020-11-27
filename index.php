<html>
<head>
	
	<title>ProyectoMI</title>
	<script type="text/javascript" src="js/libs/jquery/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="js/libs/three/three.js"></script>
	<script type="text/javascript" src="js/libs/three/MTLLoader.js"></script>
	<script type="text/javascript" src="js/libs/three/OBJLoader.js"></script>
	<script type="text/javascript" src="js/mifacebook.js"></script>
	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="style.css">



	<script id="vertexShader" type="x-shader/x-vertex">
		varying vec2 vUv;
		void main() 
		{ 
		    vUv = uv;
		    gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );
		}
	</script>

	<script id="fragmentShader" type="x-shader/x-vertex"> 
		uniform sampler2D baseTexture;
		uniform float baseSpeed;
		uniform sampler2D noiseTexture;
		uniform float noiseScale;
		uniform float alpha;
		uniform float time;

		varying vec2 vUv;
		void main() 
		{
			vec2 uvTimeShift = vUv + vec2( -0.7, 1.5 ) * time * baseSpeed;	
			vec4 noiseGeneratorTimeShift = texture2D( noiseTexture, uvTimeShift );
			vec2 uvNoiseTimeShift = vUv + noiseScale * vec2( noiseGeneratorTimeShift.r, noiseGeneratorTimeShift.b );
			vec4 baseColor = texture2D( baseTexture, uvNoiseTimeShift );

			baseColor.a = alpha;
			gl_FragColor = baseColor;
		}  
	</script>
	<script type="text/javascript">

		var isPaused=false;
		var isPlaying=false;

		function shareFB() {
			var score = $("#txtScore").val();
			shareScore(score);
		}

		$(document).keyup(function(e) {
		  if (e.keyCode === 27){
		  	if (isPaused == false && isPlaying==true) {
		  		$("#PauseSpace").show();
		  		isPaused =true;
		  	}else if(isPaused==true && isPlaying==true){
		  		isPaused =false;
		  		$("#PauseSpace").hide();
		  	}		  	
		  }   
		});

		// Menu functionality script
		$(document).ready(function(){

			$('#OptionsSpace').hide();
			$('#ShareSpace').hide();
			$('#PlaySpace').hide();
			$('#LevelSelectSpace').hide();
			$('#PauseSpace').hide();


			$('#btnPlay').click(function(){
				console.log("Play");
				$('#HomeSpace').hide();
				$('#LevelSelectSpace').show();
			});


			$('#btnShare').click(function(){
				console.log("Share");
				$('#HomeSpace').hide();
				$('#ShareSpace').show();
			});

			$('#btnOptions').click(function(){
				console.log("Options");
				$('#HomeSpace').hide();
				$('#OptionsSpace').show();
			});

			

			$('#selectLVL2').click(function(){
				console.log("Play");
				$('#LevelSelectSpace').hide();
				$('#PlaySpace').show();
				isPlaying = true;
			});

			$('#selectLVL3').click(function(){
				console.log("Play");
				$('#LevelSelectSpace').hide();
				$('#PlaySpace').show();
				isPlaying = true;
			});



			$('#btnDelete').click(function(){

				if(confirm("Esta seguro de que quiere eliminar sus preferencias y/o progreso?")){
					console.log("Operacion: Eliminar. Aprobada");
					alert("Se ha borrado toda la informacion local.")
				}else{
					console.log("Operacion: Eliminar. Cancelada");
				}
			});



			$('#btnRegresarOP').click(function(){
				console.log("Play");
				$('#HomeSpace').show();
				$('#OptionsSpace').hide();
			});

			$('#btnRegresarSH').click(function(){
				console.log("Share");
				$('#HomeSpace').show();
				$('#ShareSpace').hide();
			});

			$('#btnRegresarPL').click(function(){
				console.log("Options");
				$('#HomeSpace').show();
				$('#PlaySpace').hide();
				$('#PauseSpace').hide();
				isPlaying = false;
			});

			$('#btnLVLSelect').click(function(){
				console.log("Options");
				$('#HomeSpace').show();
				$('#LevelSelectSpace').hide();
			});
		});


		var scene;
		var camera;
		var renderer;
		var controls;
		var objects = [];
		var clock;
		var deltaTime;	
		var keys = {};

		// Inicializar material Inicio

		var noiseTexture = new THREE.ImageUtils.loadTexture( 'assets/cloud.png' );
			noiseTexture.wrapS = noiseTexture.wrapT = THREE.RepeatWrapping; 
				
		var lavaTexture = new THREE.ImageUtils.loadTexture( 'assets/water.jpg' );
			lavaTexture.wrapS = lavaTexture.wrapT = THREE.RepeatWrapping; 


		var customUniforms = {
				baseTexture: 	{ type: "t", value: lavaTexture },
				baseSpeed: 		{ type: "f", value: 0.05 },
				noiseTexture: 	{ type: "t", value: noiseTexture },
				noiseScale:		{ type: "f", value: 0.5337 },
				alpha: 			{ type: "f", value: 0.8 },
				time: 			{ type: "f", value: 1.0 }
		};


		// Inicializar material Fin

		var worldGridX = [-48, -36, -24, -12, 0, 12, 24, 36, 48];

		var spawnTimer = 0;

		var gravityFactor = 7;

		var objetosPorEvaluar = [];

		var rayCaster;

		var player1;
		var player2;

		var box;
		var boxArray =[];

		var boxCount=0;

		var water;

		var worldTimer =0;

		var isWorldReady = [ false, false, false, false, false ];

		var pause = true;
		var pauseOwner = "";

		var fxVolume = 1;
		var musicVolume = 1;

		const audioListener = new THREE.AudioListener();
		const ThumpClip1 = new THREE.Audio( audioListener );
		const ThumpClip2 = new THREE.Audio( audioListener );

		$(document).ready(function() {


			$('#selectLVL1').click(function(){
				console.log("Play");
				$('#LevelSelectSpace').hide();
				$('#PlaySpace').show();
				isPlaying = true;
				pause = false;
			});

			pauseOwner = "all";
			setupScene();

			fxVolume = JSON.parse(localStorage.getItem('VolumneFX'));
			musicVolume = JSON.parse(localStorage.getItem('VolumneMusic'));

			rayCaster = new THREE.Raycaster();

			camera.rayos = [
				new THREE.Vector3( 1, 0, 0),
				new THREE.Vector3(-1, 0, 0),
				new THREE.Vector3( 0, 0, 1),
				new THREE.Vector3( 0, 0, -1)
			];

			camera.downRay = new THREE.Vector3( 0, -1, 0);

			loadOBJWithMTL("assets/", "10BoxModel3.obj", "10BoxModel3.mtl", (miObjeto) => {
				miObjeto.children[0].material.materials[2] = customMaterial;
				miObjeto.children[0].material.materials[0].shininess = 100;
				box = miObjeto;
				box.accVert = 0;

				isWorldReady[0] = true;
				isWorldReady[1] = true;
			});

			

			loadOBJWithMTL("assets/", "Player.obj", "Player.mtl", (miObjeto) => {
				miObjeto.position.z = -40;
				miObjeto.position.y = 10;
				miObjeto.rotation.y = THREE.Math.degToRad(90);
					player1 = miObjeto.clone();
					player1.position.x = -20;
					player1.accVert = 0;
					player1.canJump = true;
					player1.tag = "player";
					player1.boxPushed =0;
					player1.canMove = true;
					player1.score = 0;
					player1.PlatformHeight = 0;
					player1.yaw = 0;
					player1.forward = 0;
					scene.add(player1);

					player2 = miObjeto.clone();
					player2.position.x = 20;
					player2.accVert = 0;
					player2.canJump = true;
					player2.tag = "player";
					player2.boxPushed =0;
					player2.canMove = true;
					player2.score = 0;
					player2.PlatformHeight = 0;
					player2.yaw = 0;
					player2.forward = 0;

					scene.add(player2);

				isWorldReady[2] = true;
			});


			// Creacion de material de agua Inicio
			var customMaterial = new THREE.ShaderMaterial( 
				{
				    uniforms: customUniforms,
					vertexShader:   document.getElementById( 'vertexShader'   ).textContent,
					fragmentShader: document.getElementById( 'fragmentShader' ).textContent
				}   );

			customMaterial.opacity = 0.9;
			customMaterial.transparent = true;
			//customMaterial.side = THREE.DoubleSide;

			// Creacion de material de agua Final

			loadOBJWithMTL("assets/", "Water2.obj", "Water2.mtl", (miObjeto) => {
		
				miObjeto.position.z = -30;
					water = miObjeto;
					water.scale.y =0;
					water.ScaleRate = 0.2;
					scene.add(water);
					water.tag = "Water";
					water.children[0].material = customMaterial;
					objetosPorEvaluar.unshift(water);

				isWorldReady[3] = true;
			});

			loadOBJWithMTL("assets/", "Pecera6.obj", "Pecera6.mtl", (miObjeto) => {
				miObjeto.position.z = -30;
				miObjeto.position.y = -2;
				miObjeto.children[1].material.materials[1].opacity = 0.6;
				miObjeto.children[1].material.materials[3].opacity = 0.6;
				miObjeto.children[1].material.materials[1].transparent = true;
				miObjeto.children[1].material.materials[3].transparent = true;
				scene.add(miObjeto);

				isWorldReady[4] = true;
			});

			loadOBJWithMTL("assets/", "Walls3.obj", "Walls3.mtl", (miObjeto) => {

				miObjeto.position.z = -40;
				scene.add(miObjeto);
			});

			ThumpClip1.setVolume(fxVolume);
			ThumpClip2.setVolume(fxVolume);

			const loader = new THREE.AudioLoader();

			// load a resource
			loader.load(	
				'assets/Thump01.ogg',

				// onLoad callback
				function ( audioBuffer ) {
					// set the audio object buffer to the loaded object
					ThumpClip1.setBuffer( audioBuffer );

				},

				// onProgress callback
				function ( xhr ) {
					console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
				},

				// onError callback
				function ( err ) {
					console.log( 'An error happened' );
				}
			);

			loader.load(	
				'assets/Thump02.ogg',

				// onLoad callback
				function ( audioBuffer ) {
					// set the audio object buffer to the loaded object
					ThumpClip2.setBuffer( audioBuffer );
				},

				// onProgress callback
				function ( xhr ) {
					console.log( (xhr.loaded / xhr.total * 100) + '% loaded' );
				},

				// onError callback
				function ( err ) {
					console.log( 'An error happened' );
				}
			);




			render();

			document.addEventListener('keydown', onKeyDown);
			document.addEventListener('keyup', onKeyUp);		
		});

		function loadOBJWithMTL(path, objFile, mtlFile, onLoadCallback) {
			var mtlLoader = new THREE.MTLLoader();
			mtlLoader.setPath(path);
			mtlLoader.load(mtlFile, (materials) => {
				
				var objLoader = new THREE.OBJLoader();
				objLoader.setMaterials(materials);
				objLoader.setPath(path);
				objLoader.load(objFile, (object) => {
					onLoadCallback(object);
				});

			});
		}

		function onKeyDown(event) {
			keys[String.fromCharCode(event.keyCode)] = true;
		}

		function onKeyUp(event) {
			keys[String.fromCharCode(event.keyCode)] = false;

			var code = event.keyCode;
			if (code == 32 ) {//Space bar 

				if(player1.canMove){
					if(player1.canJump){
						player1.accVert = gravityFactor * 0.7;
						player1.canJump = false;
					}
				}

			}

			if (code == 69 ) {//Tecla E 

				spawnBox();

			}

			if(code == 27){//Esc
				if(pause){
					debugger;
					if(pauseOwner == "player 1"){
						pause=false;
						pauseOwner ="all";
					}
				}else{
					pause =true;
					pauseOwner ="player 1";
					//("El jugador " + pauseOwner + " ha pedido Pausa. Solo el puede quitar la pausa.");
				}
			}


			if(code == 80){//Tecla P
				if(pause){
					debugger;
					if(pauseOwner == "player 2"){
						pause=false;
						pauseOwner ="all";
					}
				}else{
					pause =true;
					pauseOwner ="player 2";
					//("El jugador " + pauseOwner + " ha pedido Pausa. Solo el puede quitar la pausa.");
				}
			}
		}

		function RandomIntInRange(minP, maxP){
			var min=minP; 
	    	var max=maxP;  
	    	var random = Math.floor(Math.random() * (+max - +min)) + +min; 
	    	return random;
		}

		function spawnBox(){
			if(isWorldReady[4]){
				var cubesInMaxHeight=0;
				var cubesAmount=0;
				for(var i=0; i < boxArray.length; i++){
					if(boxArray[i].isFalling ==false && boxArray[i].position.y >= 75){
						cubesInMaxHeight++;
						//console.log(cubesInMaxHeight);
					}
					cubesAmount++;
					
				}

				if(cubesInMaxHeight <5){
				
					var temp = box.clone();
					temp.position.y = player1.position.y + 50;
					temp.position.x = worldGridX[RandomIntInRange(0, worldGridX.length)];
					temp.position.z = -40;
					temp.accVert =0;
					temp.downRay = new THREE.Vector3( 0, -1, 0);
					temp.upRay = new THREE.Vector3( 0, 1, 0);
					temp.isMovable =true;
					temp.tag = "Box";
					temp.isFalling =true;
					temp.id = cubesAmount;

					objetosPorEvaluar.unshift(temp);
					boxArray.unshift(temp);

					scene.add(temp);
				}
			}
		}

		function PlayRNDThump(){

			var randomInt = RandomIntInRange(0,2);

			if(randomInt == 0){
				ThumpClip1.play();
			}else{
				ThumpClip2.play();
			}
		}

		function render() {

			ThumpClip1.setVolume(fxVolume);
			ThumpClip2.setVolume(fxVolume);
			
			requestAnimationFrame(render);
			
				deltaTime = clock.getDelta();	

				if(!pause){

					
				if(isWorldReady[0] && isWorldReady[1] && isWorldReady[2] && isWorldReady[3] && isWorldReady[4]){
					worldTimer += deltaTime*1;

					player1.forward =0; 
					player1.yaw =0; 

					player2.forward =0; 
					player2.yaw =0; 

					if(player1.canMove){
						if (keys["A"]) {
							//yaw = 5;
							player1.forward = -20;
						} else if (keys["D"]) {
							//yaw = -5;
							player1.forward = 20;
						}
						if (keys["W"]) {
							if(player1.canMove){
								if(player1.canJump){
									player1.accVert = gravityFactor * 0.7;
									player1.canJump = false;
								}
							}
							
						} 
					}


					if(player2.canMove){
						if (keys["J"]) {
							//yaw = 5;
							player2.forward = -20;
						} else if (keys["L"]) {
							//yaw = -5;
							player2.forward = 20;
						}
						if (keys["I"]) {
							if(player2.canMove){
								if(player2.canJump){
									player2.accVert = gravityFactor * 0.7;
									player2.canJump = false;
								}
							}
							
						}
					}


					if(worldTimer >= 20){
						if(player1.canMove) water.scale.y += deltaTime * water.ScaleRate;
						if(water.ScaleRate < 0.5){
							water.ScaleRate += 0.01;
						}
					}
				}


				if(spawnTimer >= 1){
					//spawnBox();
					spawnTimer = 0;
				}
				
				
				spawnTimer += deltaTime;
				customUniforms.time.value += deltaTime;
				
				
				//Check collisions
				if (isWorldReady[0] && isWorldReady[1] && isWorldReady[2] && isWorldReady[3]) {
					console.log(player1.score + " Points");

				/*	//calculate camera player height dif and adjust accordignly
					var difY = (player1.position.y) - camera.position.y;
					var difX = player1.position.x - camera.position.x;
					var difZ = player1.position.z - camera.position.z;
					if(difY > 5){
						camera.position.y+= 0.2;
					}else if(difY < -5){
						camera.position.y-= 0.2;
					}
					if(difX > 8){
						camera.position.x+= 0.2;
					}else if(difX < -8){
						camera.position.x-= 0.2;
					}*/

					//Calcular punto medio y comparar contra la camara

					var midPX = (player1.position.x + player2.position.x)/2;
					var midPY = (player1.position.y + player2.position.y)/2;
					var midPZ = (player1.position.z + player2.position.z)/2;

					//midPX += (player1.position.x + player2.position.x)/2;
					//midPX += (player1.position.y +player2.position.y)/2;

					camera.lookAt(new THREE.Vector3(midPX,midPY,-40));

					//dif

					var difX = midPX - camera.position.x;
					var difY = midPY - camera.position.y;
					var difZ = midPZ - camera.position.z;

					console.log(difX);

					/*if(difY > 5){
						camera.position.y += 0.2;
					}else if(difY < -5){
						camera.position.y -= 0.2;
					}*/

					if(difX > 8){
						camera.position.x += 0.2;
					}else if(difX < -8){
						camera.position.x -= 0.2;
					}
			
				
					player1.rotation.y += player1.yaw * deltaTime;
					player1.translateZ(player1.forward * deltaTime);
					player1.translateY(player1.accVert * deltaTime * gravityFactor);

					player2.rotation.y += player2.yaw * deltaTime;
					player2.translateZ(player2.forward * deltaTime);
					player2.translateY(player2.accVert * deltaTime * gravityFactor);

					if (player1.position.y > 5) {
						player1.accVert-= deltaTime * gravityFactor;
						//player1.canJump =false;
					}else{
						player1.accVert=0;
						player1.canJump =true;
					}

					if (player2.position.y > 5) {
						player2.accVert-= deltaTime * gravityFactor;
						//player1.canJump =false;
					}else{
						player2.accVert=0;
						player2.canJump =true;
					}	

					//apply gravitiy to boxes and check sideways collision

					for(var i=0; i < boxArray.length; i++){
						
						if(boxArray[i].isMovable){
							boxArray[i].translateY(boxArray[i].accVert * deltaTime * gravityFactor);
						}



						for(var l =0; l < 2; l++){
							var boxPos = new THREE.Vector3(boxArray[i].position.x, boxArray[i].position.y, boxArray[i].position.z)
							
							if(l==0){
								boxPos.x += 5;
							}else{
								boxPos.x -= 5;
							}

							rayCaster.set(boxPos, camera.rayos[l]);
							var colisionL = rayCaster.intersectObjects(objetosPorEvaluar,true);

							if(colisionL.length > 0 && colisionL[0].distance < 1){

								var diferenciaCajas =  boxArray[i].position.x - colisionL[0].point.x;

								if (diferenciaCajas > 0) {

									console.log("Yo soy: " + boxArray[i].id + "  Y detecto : " + colisionL.length + " a mi izquierda");
									if (!boxArray[i].isFalling && !colisionL[0].object.parent.isFalling	) {
										boxArray[i].isMovable = false;
										colisionL[0].object.parent.isMovable = false;

									}

								}else if(diferenciaCajas < 0){

									console.log("Yo soy: " + boxArray[i].id + "  Y detecto : " + colisionL.length + " a mi derecha");
									if (!boxArray[i].isFalling && !colisionL[0].object.parent.isFalling	) {
										boxArray[i].isMovable = false;
										colisionL[0].object.parent.isMovable = false;
									}
								}

							}
						}




						//Box collision downwards
						if (boxArray[i].position.y > 0) {

							if(boxArray[i].isFalling){
								if(boxArray[i].isMovable){
									boxArray[i].accVert-= deltaTime * gravityFactor;
								}
							
								if(boxArray[i].accVert < -4) boxArray[i].accVert = -4;

							}else{
								boxArray[i].accVert = 0;
								boxArray[i].isFalling = false;

							}

							var isColiding = [false, false, false];

							for (var k =-1; k<2; k++) {

								if(boxArray[i].isMovable){
									var boxPosition = new THREE.Vector3(boxArray[i].position.x, boxArray[i].position.y, boxArray[i].position.z);

									boxPosition.x += k*6;
								
									rayCaster.set(boxPosition, boxArray[i].downRay);

									var colision = rayCaster.intersectObjects(objetosPorEvaluar, true);
									if(colision.length > 0 && colision[0].distance <= 1){

										if(colision[0].object.parent.tag != "Water"){

											if(boxArray[i].accVert <= -3) PlayRNDThump();
											
											boxArray[i].accVert=0;
											isColiding[k+1] = true;
											boxArray[i].isFalling = false;
											colision[0].object.parent.isMovable=false;
										}else{
											console.log("toque aguita que asca");
											isMovable=false;
											isFalling=false;
											boxArray[i].accVert=0;
										}
										
									}else{
										isColiding[k+1] = false;	
									}
								}
							}

							if(!isColiding[0] && !isColiding[1] && !isColiding[2]){
								boxArray[i].isFalling =true;
								
							}else{
								
								//boxArray[i].translateY(2 * deltaTime);
								boxArray[i].accVert=0;
								boxArray[i].isFalling=false
							}

						}else{
							if(boxArray[i].isFalling ==true) PlayRNDThump();
							boxArray[i].accVert = 0;
							boxArray[i].isFalling =false;
						}

					}

					//camera.lookAt(player1.position);

					var isColidingFeet = [false, false, false];


					//Check all player collisions
					for (var i = 0; i < camera.rayos.length; i ++){

						for (var k =-1; k<2; k++) {
							
							playerPosition = new THREE.Vector3(player1.position.x, player1.position.y, player1.position.z);

							playerPosition.y += k*3;
							playerPosition.x += k*0.5;

							rayCaster.set(playerPosition, camera.rayos[i]);
						
							var colision = rayCaster.intersectObjects(objetosPorEvaluar, true);
							if(colision.length > 0 && colision[0].distance < 0.5){
							
								isColidingFeet[k+1] = true;
								
								if(colision[0].object.parent.tag == "Box"){
									var direction = colision[0].point.x - colision[0].object.parent.position.x;
									player1.boxPushed = colision[0].object.parent.id;
									if(colision[0].object.parent.isMovable && colision[0].object.parent.isFalling ==false){
										if (direction > 0) {
											//mueve a la izq
											colision[0].object.parent.position.x -= deltaTime *10;
											player1.score += 1;
										}else if(direction < 0){
											//mueve a la derecha
											colision[0].object.parent.position.x += deltaTime *10;
											player1.score += 1;
										}
										player1.translateZ((player1.forward *-0.8) * deltaTime);
									}else{
										player1.translateZ((player1.forward *-1) * deltaTime);
									}
								}

								
								
							}else{
								isColidingFeet[k+1] = false;

								if(isColidingFeet[0] && isColidingFeet[1] && isColidingFeet[2]){
									boxArray[i].accVert=0;	
								}
							}

							playerPosition = new THREE.Vector3(player1.position.x, player1.position.y, player1.position.z);

							playerPosition.x += k*1;

							rayCaster.set(playerPosition, camera.downRay);

							colision = rayCaster.intersectObjects(objetosPorEvaluar, true);
							if(colision.length > 0 && colision[0].distance < 4){

								if(colision[0].object.parent.tag == "Water"){
									player1.canMove =false;
								}else{
									if(colision[0].object.parent.tag == "Box"){
										var heightdif =  player1.position.y - player1.PlatformHeight;
										//console.log("Diferencia de alt es: " + heightdif); 
										if(player1.position.y > player1.PlatformHeight && heightdif>10){
											player1.PlatformHeight= player1.position.y;
											player1.score += 50;
										}
										player1.translateY(1 * deltaTime);
										player1.accVert=0;
										player1.canJump =true;
									}
								}
								
								
								
								
							}

							//Colisiones de la cabeza
							playerPosition.y += 3;
							playerPosition.x += k*0.5;
							rayCaster.set(playerPosition, new THREE.Vector3(0, 1, 0));
							
							var colision = rayCaster.intersectObjects(objetosPorEvaluar, true);
							if(colision.length > 0 && colision[0].distance < 0.5){
								//console.log("Empuja hacia abajo");
								player1.accVert = -1;
								player1.position.y -= deltaTime*3;
								if(colision[0].object.parent.isFalling && colision[0].object.parent.accVert <= -3 && colision[0].object.parent.isMovable){
									//console.log("Ouch, i was hit by a " + colision[0].object.parent.isFalling + " at an acc of " + colision[0].object.parent.accVert);
									player1.canMove =false;
								}
							}
						}
					}

					//Check all player collisions
					for (var i = 0; i < camera.rayos.length; i ++){

						for (var k =-1; k<2; k++) {
							
							playerPosition = new THREE.Vector3(player2.position.x, player2.position.y, player2.position.z);

							playerPosition.y += k*3;
							playerPosition.x += k*0.5;

							rayCaster.set(playerPosition, camera.rayos[i]);
						
							var colision = rayCaster.intersectObjects(objetosPorEvaluar, true);
							if(colision.length > 0 && colision[0].distance < 0.5){
							
								isColidingFeet[k+1] = true;
								
								if(colision[0].object.parent.tag == "Box"){
									var direction = colision[0].point.x - colision[0].object.parent.position.x;
									player2.boxPushed = colision[0].object.parent.id;
									if(colision[0].object.parent.isMovable && colision[0].object.parent.isFalling ==false){
										if (direction > 0) {
											//mueve a la izq
											colision[0].object.parent.position.x -= deltaTime *10;
											player2.score += 1;
										}else if(direction < 0){
											//mueve a la derecha
											colision[0].object.parent.position.x += deltaTime *10;
											player2.score += 1;
										}
										player2.translateZ((player2.forward *-0.8) * deltaTime);
									}else{
										player2.translateZ((player2.forward *-1) * deltaTime);
									}
								}

								
								
							}else{
								isColidingFeet[k+1] = false;

								if(isColidingFeet[0] && isColidingFeet[1] && isColidingFeet[2]){
									boxArray[i].accVert=0;	
								}
							}

							playerPosition = new THREE.Vector3(player2.position.x, player2.position.y, player2.position.z);

							playerPosition.x += k*1;

							rayCaster.set(playerPosition, camera.downRay);

							colision = rayCaster.intersectObjects(objetosPorEvaluar, true);
							if(colision.length > 0 && colision[0].distance < 4){

								if(colision[0].object.parent.tag == "Water"){
									player2.canMove =false;
								}else{
									if(colision[0].object.parent.tag == "Box"){
										var heightdif =  player2.position.y - player2.PlatformHeight;
										//console.log("Diferencia de alt es: " + heightdif); 
										if(player2.position.y > player2.PlatformHeight && heightdif>10){
											player2.PlatformHeight= player2.position.y;
											player2.score += 50;
										}
										player2.translateY(1 * deltaTime);
										player2.accVert=0;
										player2.canJump =true;
									}
								}
								
								
								
								
							}

							//Colisiones de la cabeza
							playerPosition.y += 3;
							playerPosition.x += k*0.5;
							rayCaster.set(playerPosition, new THREE.Vector3(0, 1, 0));
							
							var colision = rayCaster.intersectObjects(objetosPorEvaluar, true);
							if(colision.length > 0 && colision[0].distance < 0.5){
								//console.log("Empuja hacia abajo");
								player2.accVert = -1;
								player2.position.y -= deltaTime*3;
								if(colision[0].object.parent.isFalling && colision[0].object.parent.accVert <= -3 && colision[0].object.parent.isMovable){
									//console.log("Ouch, i was hit by a " + colision[0].object.parent.isFalling + " at an acc of " + colision[0].object.parent.accVert);
									player2.canMove =false;
								}
							}
						}
					}	
				}
			}
			renderer.render(scene, camera);
		}

		function setupScene() {	

			var visibleSize = { width: 700, height: 400};
			clock = new THREE.Clock();		
			scene = new THREE.Scene();
			camera = new THREE.PerspectiveCamera(75, visibleSize.width / visibleSize.height, 0.1, 320);
			camera.position.z = 20;
			camera.position.y = 20;
		

			renderer = new THREE.WebGLRenderer( {precision: "mediump" } );
			renderer.setClearColor(new THREE.Color(0, 0, 0));
			renderer.setPixelRatio(visibleSize.width / visibleSize.height);
			renderer.setSize(visibleSize.width, visibleSize.height);

			var ambientLight = new THREE.AmbientLight(new THREE.Color(1, 1, 1), 1.0);
			scene.add(ambientLight);

			var directionalLight = new THREE.DirectionalLight(new THREE.Color(0.9, 1, 0.4), 0.4);
			directionalLight.position.set(0, 0, 1);
			scene.add(directionalLight);

			var grid = new THREE.GridHelper(50, 10, 0xffffff, 0xffffff);
			grid.position.y = -1;
			scene.add(grid);

			$("#scene-section").append(renderer.domElement);
		}
	</script>
</head>

<body>

	<div class="bg-image"></div>

	<div id="MenuSpace">
		<div class="Title">
			<div id="TitleText">
				<h1>Cubed Out!</h1>
				<br>
				<p>Reach the top to survive!</p>
			</div>
		</div>

		<div class="container">
		  <div class="row">
		    <div class="col-sm-1">
		    </div>
		    <div class="col-sm-10">
		    	<div class="parent" style="align-content: center;">
			    	<div class="menu" id="HomeSpace">
				      	<button class="menuBtn" id="btnPlay">Play</button>
				      	<br>
				      	<button class="menuBtn" id="btnShare">Puntuacion</button>
				      	<br>
				      	<button class="menuBtn" id="btnOptions">Options</button>
			    	</div>

			    	<div class="menu center" id="OptionsSpace">
				      	<h2 id="menuSub">Options</h2>
				      	<br>
				   
				      	 <div class="slidecontainer">
				      	 	<p style="font-size: 200%">Music Volume</p>
				      	   <input type="range" name="" style="width: 60%;">
				      	 </div>

				      	 <br><br>

				      	  <div class="slidecontainer">
				      	 	<p style="font-size: 200%">Effects Volume</p>
				      	   <input type="range" name="" style="width: 60%;">
				      	 </div>

				      	<button class="screenBtn" id="btnDelete" style="margin-top: 4%;">Eliminar Datos locales</button>

				      	<button class="screenBtn" id="btnRegresarOP" style="margin-top: 15%;">Back</button>
			    	</div>

			    	<div class="menu center" id="ShareSpace">
				      	<h2 id="menuSub">Leaderboard</h2>
				    	<br>
				      	<!-- TO DO:
							Permitir compartir puntuacion mediante facebook
				      	 -->


				      	<?php 
				      	 	include("DBConnect.php");
				      	 	$con = Connect();
				      	 	//echo "...CONECTANDO A LA BASE";

				      	 	$sql = "SELECT * FROM Score 
				      	 			ORDER BY scoreValue DESC";
				      	 	$result = mysqli_query($con, $sql) or die("<b>Error:</b> Error al conseguir el tablero: <br/>" . mysqli_error($con));

				      	 	mysqli_close($con);
				      	 	$rank = 0;
				      	 	while($row = mysqli_fetch_assoc($result)){
				      	 		$rank++;
				      	 		

				      	 		if($rank <6){	
				      	 			$con = Connect();
				      	 			//echo "...ACTUALIZANDO TABLERO";

				      	 			$sql = "call AssignRank('$row[nameScore]', $row[ScoreValue])";
				      	 			$result2 = mysqli_query($con, $sql) or die("<b>Error:</b> Error al conseguir el tablero: <br/>" . mysqli_error($con));

				      	 			mysqli_close($con);

				      	 			//echo "<div class="leaderboardItem"><p style="height: 100%; width: 60%;">$rank)  $row[nameScore]--------------- $row[ScoreValue]</p></div>";
				      	 			echo "<div class='leaderboardItem'><p style='height: 100%; width: 60%;'>$rank)  $row[nameScore]--------------- $row[ScoreValue]</p></div>";
				      	 		}
				      	
				      	 	}

				      	?>
				      	
				      	<button onclick="shareFB();">Compartir en Facebook</button>
				      	<button class="screenBtn" id="btnRegresarSH" style="margin-top: 15%;">Back</button>
			    	</div>

			    	<div class="menu center play" id="LevelSelectSpace">
				    	<!-- <p>Seleccion de nivel</p> -->

				      	<div id="SelectionDiv">
					      	<div id="levelSelectDiv">
					      		<img src="assets/Images/DefaultTHB.png" style="width: 80%; height: 70%; margin-left: 10%; margin-top: 2%;">
					      		<button id="selectLVL1" class="btnSelectScene">Select</button>
					      	</div>

					      	<div id="levelSelectDiv">
					      		<img src="https://www.contentviewspro.com/wp-content/uploads/2017/07/default_image.png" style="width: 80%; height: 70%; margin-left: 10%; margin-top: 2%;">
					      		<button id="selectLVL2" class="btnSelectScene">Select</button>
					      	</div>

					      	<div id="levelSelectDiv">
					      		<img src="https://www.contentviewspro.com/wp-content/uploads/2017/07/default_image.png" style="width: 80%; height: 70%; margin-left: 10%; margin-top: 2%;">
					      		<button id="selectLVL3" class="btnSelectScene">Select</button>
					      	</div>
				      	</div>
				      	
				      	<button class="screenBtn" id="btnLVLSelect" style="margin-top: 15%;">Back</button>
			      	</div>

			      	<div class="menu center play" id="PlaySpace">
				      	<!-- <p>Area de juego</p> -->
				      	<br>
				      	<div id="scene-section"/>
			      	</div>

			      <div class="menu center play" id="PauseSpace" style="background-color: white; position: absolute;">
			      	<!-- <p>Area de juego</p> -->
			      	<br>
			      	<button class="screenBtn" id="btnRegresarPL">Back</button>
			      </div>

			    </div>
		    </div>
		    <div class="col-sm-1">
		    </div>
		  </div>
		</div>
	</div>

</body>
</html>