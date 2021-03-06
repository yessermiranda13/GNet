<?php
    $CurrentID = "8415";
    @session_start();
    include (@$_SESSION['getConsts']);
?>
<style type="text/css">
        #mynetwork {
            width: 100%;
            height: 510px;
            border: 1px solid lightgray;
        }
        #loadingBar {
            position:absolute;
            top:0px;
            left:0px;
            width: 1072px;
            height: 512px;
            background-color:rgba(200,200,200,0.8);
            -webkit-transition: all 2.5s ease;
            -moz-transition: all 2.5s ease;
            -ms-transition: all 2.5s ease;
            -o-transition: all 2.5s ease;
            transition: all 2.5s ease;
            opacity:1;
        }
        #wrapper {
            position:relative;
            width:100%;
            height:510px;
        }

        #text {
            position:absolute;
            top:8px;
            left:530px;
            width:30px;
            height:50px;
            margin:auto auto auto auto;
            font-size:22px;
            color: #000000;
        }


        div.outerBorder {
            position:relative;
            top:400px;
            width:600px;
            height:44px;
            margin:auto auto auto auto;
            border:8px solid rgba(0,0,0,0.1);
            background: rgb(252,252,252); /* Old browsers */
            background: -moz-linear-gradient(top,  rgba(252,252,252,1) 0%, rgba(237,237,237,1) 100%); /* FF3.6+ */
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(252,252,252,1)), color-stop(100%,rgba(237,237,237,1))); /* Chrome,Safari4+ */
            background: -webkit-linear-gradient(top,  rgba(252,252,252,1) 0%,rgba(237,237,237,1) 100%); /* Chrome10+,Safari5.1+ */
            background: -o-linear-gradient(top,  rgba(252,252,252,1) 0%,rgba(237,237,237,1) 100%); /* Opera 11.10+ */
            background: -ms-linear-gradient(top,  rgba(252,252,252,1) 0%,rgba(237,237,237,1) 100%); /* IE10+ */
            background: linear-gradient(to bottom,  rgba(252,252,252,1) 0%,rgba(237,237,237,1) 100%); /* W3C */
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fcfcfc', endColorstr='#ededed',GradientType=0 ); /* IE6-9 */
            border-radius:72px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
        }

        #border {
            position:absolute;
            top:10px;
            left:10px;
            width:500px;
            height:23px;
            margin:auto auto auto auto;
            box-shadow: 0px 0px 4px rgba(0,0,0,0.2);
            border-radius:10px;
        }

        #bar {
            position:absolute;
            top:0px;
            left:0px;
            width:20px;
            height:20px;
            margin:auto auto auto auto;
            border-radius:11px;
            border:2px solid rgba(30,30,30,0.05);
            background: rgb(0, 173, 246); /* Old browsers */
            box-shadow: 2px 0px 4px rgba(0,0,0,0.4);
        }
    </style>


<script type="text/javascript">
    var nodes = null;
    var edges = null;
    var network = null;
    var popupMenu = undefined;

    var clusterIndex = 0;
    var clusters = [];
    var lastClusterZoomLevel = 0;
    var clusterFactor = 0.9;

    function destroy() {
        if (network !== null) {
            network.destroy();  
            network = null;
        }
    }

    var DIR = '<?php echo PDS_DESKTOP_ROOT; ?>/src/vis/img/refresh-cl/news/';
    var EDGE_LENGTH_MAIN = 150;
    var EDGE_LENGTH_SUB = 50;

    // Called when the Visualization API is loaded.
    function draw() {
        destroy();
        // Create a data table with nodes.
        nodes = [];

        var arrayNet = [];
        var arrayHost = [];
        var arrayRouter = [];

        LongArrayNet  = arrayNet.length;
        LongArrayHost = arrayHost.length;
        LongArrayRou  = arrayRouter.length; 

          // Create a data table with links.
        edges = [];

        //Correcto | Networks
        <?php
            #Se obtienen las direcciones de red.
            $ReturnIPNets   = $CN->getIPNet();

            if ($ReturnIPNets->num_rows > 0){
                while ($RIP = $ReturnIPNets->fetch_array(MYSQLI_ASSOC)){
                    $RIPValue = implode("", explode("/", implode("", explode(".", $RIP['ip_net']))));
                    $Switches = $CN->getHostTypeSwitch($RIP['ip_net']);

                    $RIPValue_Alias = !empty($RIP['alias']) ? $RIP['alias'] : $RIP['ip_net'];

                    if ($Switches->num_rows >= 1){
                        ?>
                            nodes.push({id: <?php echo $RIPValue; ?>, label: "<?php echo $RIPValue_Alias; ?>", image: DIR + 'switchs/switchicon1.png', shape: 'image', group: "IPNet"});
                        <?php
                    }
                }
            }
        ?>

        //Correcto | Routers with network next 
        <?php
            $Routers = $CN->getHostTypeRouter();
            while ($RRouter = $Routers->fetch_array(MYSQLI_ASSOC)){
                
                if ($RRouter['net_next'] != "-"){
                    $IDRouter = implode("", explode(".", $RRouter['ip_host']));
                    $RIPValueSwitch = implode("", explode("/", implode("", explode(".", $RRouter['ip_net']))));
                    
                    $RIPValueRouter_Alias = !empty($RRouter['alias']) ? $RRouter['alias'] : $RRouter['ip_host'];
                    $RIPValueRouterAddr = $RRouter['ip_host'];

                    ?>
                        nodes.push({id: <?php echo $IDRouter; ?>, label: "<?php echo $RIPValueRouter_Alias; ?>", ip_addr: "<?php echo $RIPValueRouterAddr; ?>", image: DIR + 'routers/router2.png', shape: 'image', group: "Routers"});
                    <?php                    
                }
            }
        ?>

        //Correcto | Devices that are not routers.
        <?php
            $Machines = $CN->getHostTypeHost();
            while ($rm = $Machines->fetch_array(MYSQLI_ASSOC)){
                $RMValue        = implode("", explode(".", $rm['ip_host']));
                $RMValueSwitch  = implode("", explode("/", implode("", explode(".", $rm['ip_net']))));

                $RMValue_Alias = !empty($rm['alias']) ? $rm['alias'] : $rm['ip_host'];
                $RMValue_Addr = $rm['ip_host'];

                if ($CN->getEachAdapterIP() == $rm['ip_host']){
                    ?>
                        nodes.push({id: <?php echo $RMValue; ?>, label: "<?php echo $RMValue_Alias; ?>", ip_addr: "<?php echo $RMValue_Addr; ?>", image: DIR + 'servers/server1.png', shape: 'image'});
                    <?php
                } else {
                    ?>
                        nodes.push({id: <?php echo $RMValue; ?>, label: "<?php echo $RMValue_Alias; ?>", ip_addr: "<?php echo $RMValue_Addr; ?>", image: DIR + 'computers/laptop1.png', shape: 'image', group: "Devices"});
                    <?php
                }
            }
        ?>

        //Connections
        <?php
            $ExtgetIPNet = $CN->getIPNet();

            if ($ExtgetIPNet->num_rows > 0){
                while ($ExtGIPN = $ExtgetIPNet->fetch_array(MYSQLI_ASSOC)){
                    $ExtGIPNValue   = implode("", explode("/", implode("", explode(".", $ExtGIPN['ip_net']))));
                    $Switches       = $CN->getHostTypeSwitch($ExtGIPN['ip_net']);

                    if ($Switches->num_rows >= 1){
                        $RecorrerHosts = $CN->getHostNetwork($ExtGIPN['ip_net']);

                        if ($RecorrerHosts->num_rows > 0){
                            while ($RH = $RecorrerHosts->fetch_array(MYSQLI_ASSOC)){
                                $RHValue = implode("", explode(".", $RH['ip_host']));
                                // echo "Host: ".$RH['ip_host']." Cambio: ".$RHValue."<br/>";
                                ?>
                                    edges.push({from: <?php echo $RHValue; ?>, to: <?php echo $ExtGIPNValue; ?>, length: EDGE_LENGTH_SUB});
                                <?php
                            }
                        }
                    }
                }
            }

            $LastRouter = $CN->getHostTypeRouterLast();
            if ($LastRouter->num_rows > 0){
                $LastRouterDato = $LastRouter->fetch_array(MYSQLI_ASSOC);
                $IDLastRouter = implode("", explode(".", $LastRouterDato['ip_host']));
                $LastRouterSwitch = implode("", explode("/", implode("", explode(".", $LastRouterDato['net_next']))));

                ?>
                    edges.push({from: <?php echo $LastRouterSwitch; ?>, to: <?php echo $IDLastRouter; ?>, length: EDGE_LENGTH_SUB});
                <?php
            }

            //Recorremos los enrutadores para saber quienes son los siguientes conectados.
            $TypeRouter = $CN->getHostTypeRouter();
            if ($TypeRouter->num_rows > 0){

                while ($TypeRouterDato = $TypeRouter->fetch_array(MYSQLI_ASSOC)){
                    $MyNetNext = $TypeRouterDato['net_next'];

                    //Se recorre nuevamente la misma tabla para averiguar

                    $FindRouterNext = $CN->getHostTypeRouter();
                    while ($Resultadin = $FindRouterNext->fetch_array(MYSQLI_ASSOC)){
                        if ($MyNetNext == $Resultadin['ip_net']){

                            $MyIDNetNext = implode("", explode(".", $TypeRouterDato['ip_host']));
                            $OtherRouter = implode("", explode(".", $Resultadin['ip_host']));

                            ?>
                                edges.push({from: <?php echo $MyIDNetNext; ?>, to: <?php echo $OtherRouter; ?>, length: EDGE_LENGTH_SUB});
                            <?php
                        }
                    }
                }
            }
        ?>

        //Connect to IP Route Local.
        <?php
            if (!is_bool($CN->getIPFirstRouter())){
                $IPFirstHost = implode("", explode(".", $CN->getIPFirstRouter()));
				
				$getIPRouterLocalVals = $CN->getIpRouteLocal();

				$console = "";
				$getIPRouterLocalVals = explode("\n", trim($getIPRouterLocalVals));
				
				foreach($getIPRouterLocalVals as $value){
					$console = $console.$value." - ";
				}

				$IPNetworkHostOnlyDS = implode("", explode("/", implode("", explode(".", $getIPRouterLocalVals[(count($getIPRouterLocalVals) - 2)]))));
				$IPNetworkBridge = implode("", explode("/", implode("", explode(".", $getIPRouterLocalVals[(count($getIPRouterLocalVals) - 3)]))));
				$IPNetworkNAT = implode("", explode("/", implode("", explode(".", $getIPRouterLocalVals[(count($getIPRouterLocalVals) - 4)]))));

				?>
					console.log("<?php echo $console.' ~ '.$IPNetworkHostOnlyDS." ~ Count: ".count($getIPRouterLocalVals)." ~ First Router: ".$IPFirstHost; ?>");
					console.log("IP Final: <?php echo $getIPRouterLocalVals[(count($getIPRouterLocalVals) - 2)]; ?> IP ID: <?php echo implode("", explode("/", implode("", explode(".", $getIPRouterLocalVals[(count($getIPRouterLocalVals) - 2)])))); ?> IP Selected: <?php echo $CN->getEachAdapterIP(); ?> IP First host: <?php echo $CN->getIPFirstRouter(); ?> IP ID First Host: <?php echo implode("", explode(".", $CN->getIPFirstRouter())); ?>");
					edges.push({from: <?php echo $IPNetworkHostOnlyDS; ?>, to: <?php echo $IPFirstHost; ?>, length: EDGE_LENGTH_SUB});
					edges.push({from: <?php echo $IPNetworkBridge; ?>, to: <?php echo $IPFirstHost; ?>, length: EDGE_LENGTH_SUB});
					edges.push({from: <?php echo $IPNetworkNAT; ?>, to: <?php echo $IPFirstHost; ?>, length: EDGE_LENGTH_SUB});
				<?php
            }

           
        //    exit();
        ?>

        /*Si no se han detectado enrutadores, se agrega uno por omisión indicando que es una LAN*/
        <?php
            if (!$CN->CheckRouterExists()){
                ?>
                    nodes.push({id: <?php echo $CurrentID; ?>, label: "LAN", ip_addr: "192.168.0.1", image: DIR + 'routers/router2.png', shape: 'image', group: "Routers"});
                <?php
                
                $ExtgetIPNet = $CN->getIPNet();
                
                if ($ExtgetIPNet->num_rows > 0){
                    while ($ExtGIPN = $ExtgetIPNet->fetch_array(MYSQLI_ASSOC)){
                        $ExtGIPNValue = implode("", explode("/", implode("", explode(".", $ExtGIPN['ip_net']))));
                        
                        ?>
                            edges.push({from: <?php echo $ExtGIPNValue; ?>, to: <?php echo $CurrentID; ?>, length: EDGE_LENGTH_SUB});
                        <?php
                    }
                }
            }
		?>
		
		// Legend
		var x = - mynetwork.clientWidth / 2 + 50;
		var y = - mynetwork.clientHeight / 2 + 50;
		var step = 80;
		nodes.push({id: 1000, x: x, y: y, label: 'Enrutador', group: 'internet', value: 1, fixed: true, physics:false});
		nodes.push({id: 1001, x: x, y: y + step, label: 'Conmutador', group: 'switch', value: 1, fixed: true,  physics:false});
		nodes.push({id: 1002, x: x, y: y + 2 * step, label: 'Servidor', group: 'server', value: 1, fixed: true,  physics:false});
		nodes.push({id: 1003, x: x, y: y + 3 * step, label: 'Ordenador', group: 'desktop', value: 1, fixed: true,  physics:false});

        // create a network
        var container = document.getElementById('mynetwork');
        var data = {
            nodes: nodes,
            edges: edges
        };
       
        // var options = {};
        var options = {
            autoResize: true,
            height: '100%',
            width: '100%',
            nodes: {
				shadow:true
            },
            edges: {
                width: 2,
                shadow:true
            },
            physics:{
				forceAtlas2Based: {
					gravitationalConstant: -26,
					centralGravity: 0.005,
					springLength: 230,
					springConstant: 0.18
				},
				maxVelocity: 146,
				solver: 'forceAtlas2Based',
				timestep: 5.35,
				stabilization: {
					enabled:true,
					iterations:2000,
					updateInterval:125
				}
			},
			groups: {
				'switch': {
					image: DIR + 'switchs/switchicon1.png',
					shape: 'image',
				},
				desktop: {
					shape: 'image',
					image: DIR + 'computers/laptop1.png',
				},
				server: {
					shape: 'image',
					image: DIR + 'servers/server1.png',
				},
				internet: {
					shape: 'image',
					image: DIR + 'routers/router2.png',
				}
			},
            layout: {randomSeed: 8},
            physics:{adaptiveTimestep:false},
            interaction: {
                navigationButtons: true,
                keyboard: true
            }
        };
        network = new vis.Network(container, data, options);
    
        // set the first initial zoom level
        network.once('initRedraw', function() {
            if (lastClusterZoomLevel === 0) {
                lastClusterZoomLevel = network.getScale();
            }
        });

        // we use the zoom event for our clustering
        network.on('zoom', function (params) {
            if (params.direction == '-') {
                if (params.scale < lastClusterZoomLevel*clusterFactor) {
                    makeClusters(params.scale);
                    lastClusterZoomLevel = params.scale;
                }
            }
            else {
                openClusters(params.scale);
            }
        });

        // if we click on a node, we want to open it up!
        network.on("selectNode", function (params) {
            // console.log("Heee, let's stay together - Select node");
            if (params.nodes.length == 1) {
                if (network.isCluster(params.nodes[0]) == true) {
                    network.openCluster(params.nodes[0])
                }
            }
        });

        // make the clusters
        function makeClusters(scale) {
            var clusterOptionsByData = {
                processProperties: function (clusterOptions, childNodes) {
                    clusterIndex = clusterIndex + 1;
                    var childrenCount = 0;
                    for (var i = 0; i < childNodes.length; i++) {
                        childrenCount += childNodes[i].childrenCount || 1;
                    }
                    clusterOptions.childrenCount = childrenCount;
                    clusterOptions.label = "# " + childrenCount + "";
                    clusterOptions.font = {size: childrenCount*5+15}
                    clusterOptions.id = 'cluster:' + clusterIndex;
                    clusters.push({id:'cluster:' + clusterIndex, scale:scale});
                    return clusterOptions;
                },
                clusterNodeProperties: {borderWidth: 3, image: DIR + 'computers/Cloud-Network.png', shape: 'image', font: {size: 30}}
            }
            network.clusterOutliers(clusterOptionsByData);
            if (document.getElementById('stabilizeCheckbox').checked === true) {
                // since we use the scale as a unique identifier, we do NOT want to fit after the stabilization
                network.setOptions({physics:{stabilization:{fit: false}}});
                network.stabilize();
            }
        }

        // open them back up!
        function openClusters(scale) {
            var newClusters = [];
            var declustered = false;
            for (var i = 0; i < clusters.length; i++) {
                if (clusters[i].scale < scale) {
                    network.openCluster(clusters[i].id);
                    lastClusterZoomLevel = scale;
                    declustered = true;
                }
                else {
                    newClusters.push(clusters[i])
                }
            }
            clusters = newClusters;
            if (declustered === true && document.getElementById('stabilizeCheckbox').checked === true) {
                // since we use the scale as a unique identifier, we do NOT want to fit after the stabilization
                network.setOptions({physics:{stabilization:{fit: false}}});
                network.stabilize();
            }
        }

        network.on('select', function(params) {
            if (popupMenu !== undefined) {
                popupMenu.parentNode.removeChild(popupMenu);
                popupMenu = undefined;
            }
            
            var nodeID = params.nodes[0];
            if (nodeID) {
                var clickedNode = this.body.nodes[nodeID];
                // $("#selection").html(params.nodes);
                $("#Topology_host_selected_id").html(params.nodes);
                $("#Topology_host_selected_ip_host").html(clickedNode.options.ip_addr);
            } else {
                $("#Topology_host_selected_ip_host").html("");
            }
            // console.log("Heee, let's stay together - Select");
        });

        // add event listeners
        $(container).click(function(){
            // No remover esto.
            document.getElementById("ContextMenuTest_White").style.visibility = "hidden";
            document.getElementById("ContextMenuTest").style.visibility = "hidden";
            
            $("#ContextMenuTest").css("visibility", "hidden");
            $("#ContextMenuTest_White").css("visibility", "hidden");

            valueSelection = $("#Topology_host_selected_ip_host").html();
            
            if (valueSelection == ""){
                $(".btn_tracking_device").attr("disabled", "disabled");
            } else{
                $(".btn_tracking_device").removeAttr("disabled");
            }
		});
		
		network.on("stabilizationProgress", function(params) {
			var maxWidth = 496;
			var minWidth = 20;
			var widthFactor = params.iterations/params.total;
			var width = Math.max(minWidth,maxWidth * widthFactor);

			document.getElementById('bar').style.width = width + 'px';
			document.getElementById('text').innerHTML = Math.round(widthFactor*100) + '%';
		});
		network.once("stabilizationIterationsDone", function() {
			document.getElementById('text').innerHTML = '100%';
			document.getElementById('bar').style.width = '496px';
			document.getElementById('loadingBar').style.opacity = 0;
			// really clean the dom element
			setTimeout(function () {document.getElementById('loadingBar').style.display = 'none';}, 500);
		});

        network.on('stabilized', function (params) {
            document.getElementById('stabilization').innerHTML = 'Stabilization took ' + params.iterations + ' iterations.';
        });

        network.on('startStabilization', function (params) {
            document.getElementById('stabilization').innerHTML = 'Stabilizing...';
        });
      
        container.addEventListener('contextmenu', function(e) {
                // getCoords = getCoordsPosition(e);

                valueSelection = document.getElementById('Topology_host_selected_ip_host').innerHTML;
                if (valueSelection == ""){
                    popupMenux = document.getElementById("ContextMenuTest_White");
                    // document.getElementById("btn_tracking_b2").setAttribute("disabled", "disabled");
                } else {
                    // document.getElementById("btn_tracking_b2").removeAttribute("disabled");
                    popupMenux = document.getElementById("ContextMenuTest");
                }

                var offsetX = e.offsetX;
                var offsetY = e.offsetY;

                if (e.target != this){ // 'this' is our HTMLElement
                    offsetX = e.target.offsetLeft + e.offsetX;
                    offsetY = e.target.offsetTop + e.offsetY;
                }

                // alert("X: " + offsetX + ", Y: " + offsetY);

                popupMenux.style.left = offsetX + 'px';
                popupMenux.style.top = offsetY + 'px';
                // popupMenux.style.left = getCoords.x - 272 + 'px';
                // popupMenux.style.top = getCoords.y - 132 + 'px';
                container.appendChild(popupMenux);
                $("#ContextMenuTest").css("visibility", "hidden");
                $("#ContextMenuTest_White").css("visibility", "hidden");
                
                popupMenux.style.visibility = "visible";
            e.preventDefault()
        }, false);
    }

</script>

<input type="hidden" style="float: right" id="ClickSondeoFinal" onclick="javascript: draw();" value="Cambiar panorama" />
<!-- <div id="mynetwork" style="width: 100%; height:510px;"></div> -->

<div id="wrapper">
    <div id="mynetwork"></div>
    <div id="loadingBar">
        <div class="outerBorder">
            <div id="text">0%</div>
            <div id="border">
                <div id="bar"></div>
            </div>
        </div>
    </div>
</div>
