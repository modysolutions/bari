let elemsHTML;
let resultadosBuscadorObserver = null;

export default {
  // inicializaDatos - INI
  inicializaDatos() {
    elemsHTML = {
      filtro: {
        todos: document.querySelector(".new-sidebar-filter-search"),
        grupo: document.querySelectorAll(".new-search-group"),
        available: [],
        contentType: [],
        geography: [],
        topics: [],
        // activos: [],
        idsActivos: [],
        date: 'all',
        mobile: document.querySelector(".new-sidebar-filter-title-search-mobile"),
        mobileSidebar: document.querySelector(".sidebar"),
        mobileCloseFilter: document.querySelector(".new-sidebar-close-filter-search-mobile"),
        mobileContinueFilter: document.querySelector(".new-sidebar-continue-filter-search-mobile"),
        availableActive: false,
        contentTypeActive: true,
        geographyActive: true,
        topicsActive: true,
      },
      resultados: {
        todos: document.querySelector(".common-search-results").querySelectorAll(".new-search-card"),
        contenido: document.querySelector(".common-search-results"),
        infinite: document.querySelector(".infinite-loader-oberserver"),
      },
    };
    this.inicializaOpciones();
  },
  inicializaOpciones() {

    let totalResultados = elemsHTML.resultados.todos.length;
    let contador = 0;
    elemsHTML.resultados.todos.forEach((opcion) => {

      elemsHTML.filtro.idsActivos.push(opcion.dataset.id);
      if (contador == totalResultados - 1) {
        opcion.classList.add("last-articles-container");
      }

      // para evitar el caso en que no tiene topics
      if (opcion.dataset.lock && elemsHTML.filtro.availableActive) {
        var arrayLock = JSON.parse(opcion.dataset.lock.replace(/'/g, '"'));
        arrayLock.forEach((opcionFiltro) => {

          let insertarOpcion = true;

          elemsHTML.filtro.available.forEach((opcionFiltroAvailable) => {
            if (opcionFiltro[0] == opcionFiltroAvailable[0]) {
              insertarOpcion = false;
            }
          });

          if (insertarOpcion) {
            elemsHTML.filtro.available.push(opcionFiltro);
          }
        });
      }

      // para evitar el caso en que no tiene topics
      if (opcion.dataset.ctype && elemsHTML.filtro.contentTypeActive) {
        var arrayCtype = JSON.parse(opcion.dataset.ctype.replace(/'/g, '"'));
        arrayCtype.forEach((opcionFiltro) => {
          
          let insertarOpcion = true;

          elemsHTML.filtro.contentType.forEach((opcionFiltroAvailable) => {
            if (opcionFiltro[0] == opcionFiltroAvailable[0]) {
              insertarOpcion = false;
            }
          });

          if (insertarOpcion) {
            elemsHTML.filtro.contentType.push(opcionFiltro);
            // elemsHTML.filtro.activos.push(opcionFiltro[0]);
          }
        });
      }

      // para evitar el caso en que no tiene topics
      if (opcion.dataset.geo && elemsHTML.filtro.geographyActive) {
        var arrayGeo = JSON.parse(opcion.dataset.geo.replace(/'/g, '"'));
        arrayGeo.forEach((opcionFiltro) => {
          
          let insertarOpcion = true;
          
          elemsHTML.filtro.geography.forEach((opcionFiltroAvailable) => {
            if (opcionFiltro[0] == opcionFiltroAvailable[0]) {
              insertarOpcion = false;
            }
          });

          if (insertarOpcion) {
            elemsHTML.filtro.geography.push(opcionFiltro);
            // elemsHTML.filtro.activos.push(opcionFiltro[0]);
          }
        });
      }

      // para evitar el caso en que no tiene topics
      if (opcion.dataset.topics && elemsHTML.filtro.topicsActive) {
        
        var arrayTopics = JSON.parse(opcion.dataset.topics.replace(/'/g, '"'));
        arrayTopics.forEach((opcionFiltro) => {

          let insertarOpcion = true;
          
          elemsHTML.filtro.topics.forEach((opcionFiltroAvailable) => {
            if (opcionFiltro[0] == opcionFiltroAvailable[0]) {
              insertarOpcion = false;
            }
          });

          if (insertarOpcion) {
            elemsHTML.filtro.topics.push(opcionFiltro);
            // elemsHTML.filtro.activos.push(opcionFiltro[0]);
          }
        });
        
      } 

      elemsHTML.filtro.topics = elemsHTML.filtro.topics.sort();
      contador += 1;
    });

    elemsHTML.filtro.contentType = elemsHTML.filtro.contentType.sort();

    elemsHTML.filtro.contentType = this.sortParents(elemsHTML.filtro.contentType);
    this.iconsParents(elemsHTML.filtro.contentType);

    elemsHTML.filtro.geography = this.sortParents(elemsHTML.filtro.geography);
    this.iconsParents(elemsHTML.filtro.geography);
  },
  sortParents(dataTax) {

    var hashArrData = {};
    dataTax.forEach((data) => {

      if (hashArrData[data[4]] == undefined) {
        hashArrData[data[4]] = [];
      }
      hashArrData[data[4]].push(data);
    });

    var result = this.hierarhySort(hashArrData, 0, []);
    return result;
  },
  hierarhySort(hashArr, key, result) {
    
    if (hashArr[key] == undefined) return;
    var arr = hashArr[key].sort((a, b) => {
      return a[1] < b[1];
    });

    for (var i=0; i<arr.length; i++) {
      result.push(arr[i]);
      this.hierarhySort(hashArr, arr[i][3], result);
    }
  
    return result;
  },
  iconsParents(data) {
    data.forEach((singleData) => {
      let mostrarIcon = false;
      mostrarIcon = this.hasChild(data, singleData[3]);
      if (mostrarIcon) {
        singleData[5] = 'show-icon';
      }
    });
  },
  hasChild(data, idTax) {
    let child = false;
    data.forEach((singleDataChild) => {
      if (singleDataChild[4] == idTax) {
        child =  true;
      }
    });
    return child;
  },
  // inicializaDatos - FIN

  // pintaFiltros - INI
  pintaFiltros() {
    if (elemsHTML.filtro.availableActive) {
      const searchAccess = elemsHTML.filtro.todos.querySelector("#search-access");
      for (const opcion of elemsHTML.filtro.available) {
        const newOptionElem = this.creaOpcionFiltro(searchAccess, opcion);
        searchAccess.querySelector(".filter-search-select").appendChild(newOptionElem);
        searchAccess.querySelector(".filter-search-select").classList.add('hidden');
      }
    }

    if (elemsHTML.filtro.contentTypeActive) {
      const searchContentType = elemsHTML.filtro.todos.querySelector("#search-content_type");
      for (const opcion of elemsHTML.filtro.contentType) {
        const newOptionElem = this.creaOpcionFiltro(searchContentType, opcion);
        searchContentType.querySelector(".filter-search-select").appendChild(newOptionElem);
        searchContentType.querySelector(".filter-search-select").classList.add('hidden');
      }
    }

    if (elemsHTML.filtro.geographyActive) {
      const searchGeo = elemsHTML.filtro.todos.querySelector("#search-geography");
      for (const opcion of elemsHTML.filtro.geography) {
        const newOptionElem = this.creaOpcionFiltro(searchGeo, opcion);
        searchGeo.querySelector(".filter-search-select").appendChild(newOptionElem);
        searchGeo.querySelector(".filter-search-select").classList.add('hidden');
      }
    }

    if (elemsHTML.filtro.topicsActive) {
      const searchTopic = elemsHTML.filtro.todos.querySelector("#search-topic");
      for (const opcion of elemsHTML.filtro.topics) {
        const newOptionElem = this.creaOpcionFiltro(searchTopic, opcion);
        searchTopic.querySelector(".filter-search-select").appendChild(newOptionElem);
        searchTopic.querySelector(".filter-search-select").classList.add('hidden');
      }
    }
    
  },
  creaOpcionFiltro(grupoFiltro, opcion) {
    const newOption = grupoFiltro.querySelector(".filter-search-select").querySelector(".dummy-filter-search").cloneNode(true);
    newOption.removeAttribute("hidden");
    newOption.classList.remove("dummy-filter-search");
    newOption.dataset.value = opcion[0];
    newOption.textContent = opcion[1];


    if ((grupoFiltro.id ==  'search-geography') || (grupoFiltro.id ==  'search-content_type')) {
      let idgeo, idgeoparent;

      idgeo = opcion[3];
      idgeoparent = opcion[4];

      newOption.setAttribute("data-idgeo", idgeo);
      newOption.setAttribute("data-idgeoparent", idgeoparent);

      let usa = 4;
      let canada = 5;
      let uk = 269;

      let flechaDesplegable = (opcion[5] == 'show-icon');

      if (opcion[4] == 0) {
        newOption.classList.add("continent");
      } else {
        if ((opcion[4] == usa) || (opcion[4] == canada) || (opcion[4] == uk)) {
          newOption.classList.add("hidden");
          newOption.classList.add("state");
        } else {
          // inicialmente no se ven los países
          newOption.classList.add("hidden");
          newOption.classList.add("country");
          if ((opcion[3] == usa) || (opcion[3] == canada) || (opcion[3] == uk)) {
            newOption.classList.add("country-state");
          }
        }
      }
      
      if (flechaDesplegable) {
        const flecha = document.createElement('span');
        flecha.classList.add("fa");
        flecha.classList.add("fa-chevron-down");
        flecha.classList.add("icono-filtro-geo");

        flecha.dataset.value = opcion[3];
        newOption.appendChild(flecha);
      }

    }

    return newOption;
  },
  // pintaFiltros - FIN

  // eventsFilters - INI
  eventsFilters() {
    elemsHTML.filtro.todos.addEventListener("click", (e) => {      
      this.toogleFilterResults(e);
      // console.log('IDs de después del click', elemsHTML.filtro.idsActivos);
    });

    elemsHTML.filtro.mobile.addEventListener("click", (e) => {
      elemsHTML.filtro.mobileSidebar.style = 'display: block;';
      elemsHTML.filtro.mobileCloseFilter.style = 'display: block';
      elemsHTML.filtro.mobileContinueFilter.style = 'display: block';
    });

    elemsHTML.filtro.mobileCloseFilter.addEventListener("click", (e) => {
      elemsHTML.filtro.mobileSidebar.style = 'display: none;';
      elemsHTML.filtro.mobileCloseFilter.style = 'display: none';
      elemsHTML.filtro.mobileContinueFilter.style = 'display: none';
    });

    elemsHTML.filtro.mobileContinueFilter.addEventListener("click", (e) => {
      elemsHTML.filtro.mobileSidebar.style = 'display: none;';
      elemsHTML.filtro.mobileCloseFilter.style = 'display: none';
      elemsHTML.filtro.mobileContinueFilter.style = 'display: none';
    });
  },
  // eventsFilters - FIN

  // toogleFilterResults CLICK - INI
  toogleFilterResults(e) {

    let accion;

    // para descartar clicks en otras partes del contenido
    if (!e.target.classList.contains('filter-search-select') && !e.target.classList.contains('new-search-group') && !e.target.classList.contains('new-search-group-name')) {

      if (e.target.classList.contains('icono-filtro')) {
        this.toogleName(e);
      } else {
        if (e.target.classList.contains('icono-filtro-geo')) {
          this.toogleLevels(e);
        } else {
          if (e.target.classList.contains('filter-data')) {
            // filtro por fecha
            accion = this.filterDataAction(e);
            this.updateIdsActivos(e);
          } else {
            
            if (e.target.classList.contains("active")) {
              e.target.classList.remove("active");
              accion = 'eliminar-detalle';
            } else {
              e.target.classList.add("active");
              accion = 'obtener-detalle';
            }
            
            this.updateFiltrosActivos(e, accion);
            this.updateIdsActivos(e);
          }
          this.muestraFiltros();
          this.muestraResultados();
        }
      }

    }
  },
  toogleName (e) {
    let idOpcion =  'search-' + e.target.dataset.value;
    elemsHTML.filtro.grupo.forEach((opcionFiltro) => {
      if (opcionFiltro.id == idOpcion) {
        (opcionFiltro.querySelector('.filter-search-select').classList.contains('hidden')) ? opcionFiltro.querySelector('.filter-search-select').classList.remove('hidden') : opcionFiltro.querySelector('.filter-search-select').classList.add('hidden');
        (opcionFiltro.querySelector('.icono-filtro').classList.contains('girar')) ? opcionFiltro.querySelector('.icono-filtro').classList.remove('girar') : opcionFiltro.querySelector('.icono-filtro').classList.add('girar');
      } else {
        (!opcionFiltro.querySelector('.filter-search-select').classList.contains('hidden')) ? opcionFiltro.querySelector('.filter-search-select').classList.add('hidden') : [];
        (opcionFiltro.querySelector('.icono-filtro').classList.contains('girar')) ? opcionFiltro.querySelector('.icono-filtro').classList.remove('girar') : [];
      }
    }); 
  },
  toogleLevels(e) {
    let parentContinent = e.target.dataset.value;

    // girar la flecha
    let girado = (e.target.classList.contains("girar"));
    (e.target.classList.contains("girar")) ? e.target.classList.remove("girar") : e.target.classList.add("girar");

    let toogleFromFilter = '';

    elemsHTML.filtro.geography.forEach((opcionFiltro) => {
      if (opcionFiltro[4] == parentContinent) {
        toogleFromFilter = 'geography';
      }
    });

    elemsHTML.filtro.contentType.forEach((opcionFiltro) => {
      if (opcionFiltro[4] == parentContinent) {
        toogleFromFilter = 'content-type';
      }
    });

    switch (toogleFromFilter) {
      case 'geography':
        let usa = 4;
        let canada = 5;
        let uk = 269;
        
        elemsHTML.filtro.geography.forEach((opcionFiltro) => {
      
          if (opcionFiltro[4] == parentContinent) {
            const searchGeo = elemsHTML.filtro.todos.querySelector("#search-geography").querySelector(".filter-search-select").querySelectorAll(".option-search");
            searchGeo.forEach((opcion) => {
              if (opcion.dataset.value == opcionFiltro[0]) {
                (opcion.classList.contains("hidden")) ? opcion.classList.remove("hidden") : opcion.classList.add("hidden");
                if (girado) {
                  if (opcion.querySelector('.icono-filtro-geo')) {
                    if (opcion.querySelector('.icono-filtro-geo').classList.contains("girar")) {
                      opcion.querySelector('.icono-filtro-geo').classList.remove("girar");
                    }
                  }
                }
              }
            });
    
            if (girado && ((opcionFiltro[3] == usa) || (opcionFiltro[3] == canada) || (opcionFiltro[3] == uk))) {
              const searchGeo = elemsHTML.filtro.todos.querySelector("#search-geography").querySelector(".filter-search-select").querySelectorAll(".option-search");
              searchGeo.forEach((opcion) => {
                if (opcion.dataset.idgeoparent == opcionFiltro[3]) {
                  if (!opcion.classList.contains("hidden")) {
                    opcion.classList.add("hidden");
                  }
                }
              });
            }
          }
          
        });
        break;
      case 'content-type':
        elemsHTML.filtro.contentType.forEach((opcionFiltro) => {
      
          if (opcionFiltro[4] == parentContinent) {
            const searchGeo = elemsHTML.filtro.todos.querySelector("#search-content_type").querySelector(".filter-search-select").querySelectorAll(".option-search");
            searchGeo.forEach((opcion) => {
              if (opcion.dataset.value == opcionFiltro[0]) {
                (opcion.classList.contains("hidden")) ? opcion.classList.remove("hidden") : opcion.classList.add("hidden");
                if (girado) {
                  if (opcion.querySelector('.icono-filtro-geo')) {
                    if (opcion.querySelector('.icono-filtro-geo').classList.contains("girar")) {
                      opcion.querySelector('.icono-filtro-geo').classList.remove("girar");
                    }
                  }
                }
              }
            });
          }
          
        });
        break;
    }
  
    
  },
  // toogleFilterResults CLICK - FIN

  // filterData and IDs - INI
  filterDataAction(e) {
    const searchData = elemsHTML.filtro.todos.querySelector("#search-dates").querySelector(".filter-search-select").querySelectorAll(".option-search");
    searchData.forEach((opcion) => {
      (e.target.dataset.value == opcion.dataset.value) ? opcion.classList.add("active") : opcion.classList.remove("active");
      if (e.target.dataset.value == opcion.dataset.value) {
        elemsHTML.filtro.date = e.target.dataset.value;
      } 
    });
  },
  updateFiltrosActivos(e, accion) {
    
    let estado;
    let otroEstado;
    let showAll;

    switch (accion) {
      case 'reducir':
        estado = 'hidden';
        break;
      case 'ampliar':
        estado = 'active';
        break;
      case 'obtener-detalle':
        estado = 'active';
        otroEstado = 'hidden';
        break;
      case 'eliminar-detalle':
        estado = 'hidden';
        break;
    }

    switch (e.target.dataset.type) {
      case 'access':
        showAll = true;
        elemsHTML.filtro.available.forEach((opcion) => {
          if (opcion[0] == e.target.dataset.value) {
            opcion[2] = estado; 
            if (estado == 'active') {
              showAll = false;
            } 
          } else {
            if (opcion[2] == 'show-all') {
              opcion[2] = otroEstado;
            }
            if (opcion[2] == 'active') {
              showAll = false;
            }
          }
        });
        if (showAll) {
          elemsHTML.filtro.available.forEach((opcion) => {
            opcion[2] = 'show-all'; 
          });
        }
        break;
      case 'content_type':
        showAll = true;
        elemsHTML.filtro.contentType.forEach((opcion) => {
          if (opcion[0] == e.target.dataset.value) {
            opcion[2] = estado; 
            if (estado == 'active') {
              showAll = false;
            }  
          } else {
            if (opcion[2] == 'show-all') {
              opcion[2] = otroEstado;
            }
            if (opcion[2] == 'active') {
              showAll = false;
            }
          }
        });
        if (showAll) {
          elemsHTML.filtro.contentType.forEach((opcion) => {
            opcion[2] = 'show-all'; 
          });
        }
        break;
      case 'geography':
        let idGeo;
        let idGeoParent;

        let usa = 4;
        let canada = 5;
        let uk = 269;

        elemsHTML.filtro.geography.forEach((opcion) => {
          
          idGeo = opcion[3];
          idGeoParent = opcion[4];  

          if (opcion[0] == e.target.dataset.value) {
            opcion[2] = estado;
          } else {
            if (opcion[2] == 'show-all') {
              opcion[2] = otroEstado;
            }
          }
        });

        showAll = this.mostrarTodosGeo();
        if (showAll) {
          elemsHTML.filtro.geography.forEach((opcion) => {
            opcion[2] = 'show-all'; 
          });
        }
        break;
      case 'topic':
        showAll = true;
        elemsHTML.filtro.topics.forEach((opcion) => {
          if (opcion[0] == e.target.dataset.value) {
            opcion[2] = estado;  
            if (estado == 'active') {
              showAll = false;
            } 
          } else {
            if (opcion[2] == 'show-all') {
              opcion[2] = otroEstado;
            }
            if (opcion[2] == 'active') {
              showAll = false;
            }
          }
        });
        if (showAll) {
          elemsHTML.filtro.topics.forEach((opcion) => {
            opcion[2] = 'show-all'; 
          });
        }
        break;
    }
  },
  updateIdsActivos(e) {

    elemsHTML.filtro.idsActivos = [];
    elemsHTML.resultados.todos.forEach((resultado) => {

      let visible = false;
      let visibleDate = false;
      let visibleFilter = false;

      // tratar el tema de la fecha
      visibleDate = this.lessThanMonth(resultado.dataset.date);
     
      visibleFilter = this.compruebaResultadoFiltro(resultado);
     
      if (visibleDate && visibleFilter) {
        visible = true;
      }
     
      if (visible) {
        elemsHTML.filtro.idsActivos.push(resultado.dataset.id);
      }

    });

  },
  compruebaResultadoFiltro(resultado) {

    let visibleAvailable = false;
    let visibleCType = false;
    let visibleGeo = false;
    let visibleTopic = false;
    let visible = false;

    let opcionesResultado;

    opcionesResultado = resultado.dataset.lock ? JSON.parse(resultado.dataset.lock.replace(/'/g, '"')) : [];
    opcionesResultado.forEach((opcion) => {
      elemsHTML.filtro.available.forEach((opcionFiltro) => {
        if ((opcionFiltro[0] == opcion[0]) && !visibleAvailable) {
          visibleAvailable = ((opcionFiltro[2] == 'active') || (opcionFiltro[2] == 'show-all')) ;
        }
      });
    });

    if (this.esVisibleAll (elemsHTML.filtro.available)) {
      visibleAvailable = true;
    }
    
    opcionesResultado = resultado.dataset.ctype ? JSON.parse(resultado.dataset.ctype.replace(/'/g, '"')) : [];
    opcionesResultado.forEach((opcion) => {
      elemsHTML.filtro.contentType.forEach((opcionFiltro) => {
        if ((opcionFiltro[0] == opcion[0]) && !visibleCType) {
          visibleCType = ((opcionFiltro[2] == 'active') || (opcionFiltro[2] == 'show-all'));
        }
      });
    });
    if (this.esVisibleAll (elemsHTML.filtro.contentType)) {
      visibleCType = true;
    }

    opcionesResultado = resultado.dataset.geo ? JSON.parse(resultado.dataset.geo.replace(/'/g, '"')) : [];
    visibleGeo = false;

    let mostrarGeo;

    opcionesResultado.forEach((opcion) => {
      mostrarGeo = this.esVisibleGeo (opcion[3]);
      if (mostrarGeo) {
        visibleGeo = true;
      }
    });
    if (this.esVisibleAll (elemsHTML.filtro.geography)) {
      visibleGeo = true;
    }

    opcionesResultado =  resultado.dataset.topics ? JSON.parse(resultado.dataset.topics.replace(/'/g, '"')) : [];
    opcionesResultado.forEach((opcion) => {
      elemsHTML.filtro.topics.forEach((opcionFiltro) => {
        if ((opcionFiltro[0] == opcion[0]) && !visibleTopic) {
          visibleTopic = ((opcionFiltro[2] == 'active') || (opcionFiltro[2] == 'show-all'));
          
        }
      });
    });
    if (this.esVisibleAll (elemsHTML.filtro.topics)) {
      visibleTopic = true;
    }

    if (!elemsHTML.filtro.availableActive) {
      visibleAvailable = true;
    }
    if (!elemsHTML.filtro.contentTypeActive) {
      visibleCType = true;
    }
    if (!elemsHTML.filtro.geographyActive) {
      visibleGeo = true;
    }
    
    if (!elemsHTML.filtro.topicsActive) {
      visibleTopic = true;
    }

    if (visibleAvailable && visibleCType && visibleGeo && visibleTopic) {
      visible = true;
    } 

    return visible;
  },
  // filterData and IDs - FIN

  // PublicationDate - INI
  lessThanMonth(fecha) {
    let today = new Date();
    let resultadoDate = new Date(fecha);

    switch (elemsHTML.filtro.date) {
      case '1month': 
        today.setMonth(today.getMonth() - 1);
        break;
      case '3month': 
        today.setMonth(today.getMonth() - 3);
        break;
      case '1year': 
        today.setMonth(today.getMonth() - 12);
        break;
      case 'thisyear': 
        let year = resultadoDate.getFullYear();
        let yearToday = today.getFullYear();
        if (year === yearToday) {
          return true;
        } else {
          return false;
        }
      default: // today is today ;)
        return true;
    }
    var diferenciaDates = (today.getTime() - resultadoDate.getTime());

    if (diferenciaDates < 0) {
      return true;
    }
    return false;

  },
  // PublicationDate - FIN

  // Geography - INI
  transmiteGeo(elemento, idGeo, idGeoParent, estado) {
    elemsHTML.filtro.geography.forEach((opcion) => {
      if (opcion[4] == idGeo) {
        opcion[2] = estado;  
      }
    });
    
  },
  subnivelesGeo(idGeo, estado) {
    elemsHTML.filtro.geography.forEach((opcion) => {
      if (opcion[4] == idGeo) {
        opcion[2] = estado;  
      }
    });
  },
  mostrarTodosGeo() {
    let resultado = true;
    elemsHTML.filtro.geography.forEach((opcion) => {
      if (opcion[2] == 'active') {
        resultado = false; 
      }
    });
    return resultado;
  },
  esVisibleGeo(idGeo) {
    let resultado = false;
    elemsHTML.filtro.geography.forEach((opcion) => {
      if ((opcion[2] == 'active') && ((opcion[3] == idGeo))) {
        resultado = true; 
      }
    });
    return resultado;
  },
  esVisibleAll(resultadoData) {
    let resultado = false;
    resultadoData.forEach((opcion) => {
      if (opcion[2] == 'show-all') {
        resultado = true; 
      }
    });
    return resultado;
  },
  // Geography - FIN


  // muestraResultados & muestraFiltros - INI
  muestraResultados() {
      
    elemsHTML.resultados.todos.forEach((resultado) => {

      if (elemsHTML.filtro.idsActivos.includes(resultado.dataset.id)) {
        if (resultado.classList.contains("hidden")) {
          resultado.classList.remove("hidden");
        }
      } else {
        if (resultado.classList.contains("hidden") == false) {
          resultado.classList.add("hidden");
        }
      }
      
    });
  },
  muestraFiltros() {
  
    if (elemsHTML.filtro.availableActive) {
      const searchAccess = elemsHTML.filtro.todos.querySelector("#search-access").querySelector(".filter-search-select");
      elemsHTML.filtro.available.forEach((opcion) => {
        if ((opcion[2] == 'hidden') || (opcion[2] == 'disabled') || (opcion[2] == 'show-all')) {
          searchAccess.querySelector(`[data-value="${opcion[0]}"]`).classList.remove("active");
        } else {
          searchAccess.querySelector(`[data-value="${opcion[0]}"]`).classList.add("active");
        }
      });
    }
    
    if (elemsHTML.filtro.contentTypeActive) {
      const searchContentType = elemsHTML.filtro.todos.querySelector("#search-content_type").querySelector(".filter-search-select");
      elemsHTML.filtro.contentType.forEach((opcion) => {
        if ((opcion[2] == 'hidden') || (opcion[2] == 'disabled') || (opcion[2] == 'show-all')) {
          searchContentType.querySelector(`[data-value="${opcion[0]}"]`).classList.remove("active");
          // searchContentType.querySelector(`[data-value="${opcion[0]}"]`).classList.add("disabled");
        } else {
          searchContentType.querySelector(`[data-value="${opcion[0]}"]`).classList.add("active");
        }
      });
    }
    
    if (elemsHTML.filtro.geographyActive) {
      const searchGeo = elemsHTML.filtro.todos.querySelector("#search-geography").querySelector(".filter-search-select");
      elemsHTML.filtro.geography.forEach((opcion) => {
        if ((opcion[2] == 'hidden') || (opcion[2] == 'disabled') || (opcion[2] == 'show-all')) {
          searchGeo.querySelector(`[data-value="${opcion[0]}"]`).classList.remove("active");
        } else {
          searchGeo.querySelector(`[data-value="${opcion[0]}"]`).classList.add("active");
        }
      });
    }

    if (elemsHTML.filtro.topicsActive) {
      const searchTopic = elemsHTML.filtro.todos.querySelector("#search-topic").querySelector(".filter-search-select");
      elemsHTML.filtro.topics.forEach((opcion) => {
        if ((opcion[2] == 'hidden') || (opcion[2] == 'disabled') || (opcion[2] == 'show-all')) {
          searchTopic.querySelector(`[data-value="${opcion[0]}"]`).classList.remove("active");
        } else {
          searchTopic.querySelector(`[data-value="${opcion[0]}"]`).classList.add("active");
        }
      });
    }

  },
  // muestraResultados & muestraFiltros - FIN

  init() {
    setTimeout(() => {
      if (document.querySelector(".common-search-results")) {
        this.inicializaDatos();
        this.pintaFiltros();
        this.eventsFilters();
      } else {
        document.querySelector(".sidebar-primary").classList.add("hidden");
      }
    }, 100);

  },
};
