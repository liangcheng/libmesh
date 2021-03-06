// The libMesh Finite Element Library.
// Copyright (C) 2002-2012 Benjamin S. Kirk, John W. Peterson, Roy H. Stogner

// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.

// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.

// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA



// C++ includes
#include <fstream>
#include <iomanip>

// Local includes
#include "libmesh/libmesh_config.h"
#include "libmesh/libmesh_logging.h"
#include "libmesh/tecplot_io.h"
#include "libmesh/mesh_base.h"
#include "libmesh/elem.h"

#ifdef LIBMESH_HAVE_TECPLOT_API
extern "C" {
# include <TECIO.h>
}
#endif


namespace libMesh
{


//--------------------------------------------------------
// Macros for handling Tecplot API data

#ifdef LIBMESH_HAVE_TECPLOT_API

namespace
{
  class TecplotMacros
  {
  public:
    TecplotMacros(const unsigned int n_nodes,
		  const unsigned int n_vars,
		  const unsigned int n_cells,
		  const unsigned int n_vert);
    float & nd(const unsigned int i, const unsigned int j);
    int   & cd(const unsigned int i, const unsigned int j);
    std::vector<float> nodalData;
    std::vector<int>   connData;
    //float* nodalData;
    //int*   connData;
  private:
    const unsigned int n_nodes;
    const unsigned int n_vars;
    const unsigned int n_cells;
    const unsigned int n_vert;
  };
}



inline
TecplotMacros::TecplotMacros(const unsigned int nn,
			     const unsigned int nvar,
			     const unsigned int nc,
			     const unsigned int nvrt) :
  n_nodes(nn),
  n_vars(nvar),
  n_cells(nc),
  n_vert(nvrt)
{
  nodalData.resize(n_nodes*n_vars);
  connData.resize(n_cells*n_vert);
}



inline
float & TecplotMacros::nd(const unsigned int i, const unsigned int j)
{
  return nodalData[(i)*(n_nodes) + (j)];
}



inline
int & TecplotMacros::cd(const unsigned int i, const unsigned int j)
{
  return connData[(i) + (j)*(n_vert)];
}

#endif
//--------------------------------------------------------



// ------------------------------------------------------------
// TecplotIO  members
void TecplotIO::write (const std::string& fname)
{
  if (libMesh::processor_id() == 0)
    {
      if (this->binary())
	this->write_binary (fname);
      else
	this->write_ascii  (fname);
    }
}



void TecplotIO::write_nodal_data (const std::string& fname,
				  const std::vector<Number>& soln,
				  const std::vector<std::string>& names)
{
  START_LOG("write_nodal_data()", "TecplotIO");

  if (libMesh::processor_id() == 0)
    {
      if (this->binary())
	this->write_binary (fname, &soln, &names);
      else
	this->write_ascii  (fname, &soln, &names);
    }

  STOP_LOG("write_nodal_data()", "TecplotIO");
}



void TecplotIO::write_ascii (const std::string& fname,
			     const std::vector<Number>* v,
			     const std::vector<std::string>* solution_names)
{
  // Should only do this on processor 0!
  libmesh_assert_equal_to (libMesh::processor_id(), 0);

  // Create an output stream
  std::ofstream out(fname.c_str());

  // Make sure it opened correctly
  if (!out.good())
    libmesh_file_error(fname.c_str());

  // Get a constant reference to the mesh.
  const MeshBase& mesh = MeshOutput<MeshBase>::mesh();

  // Write header to stream
  {
    {
      // TODO: We used to print out the SVN revision here when we did keyword expansions...
      out << "# For a description of the Tecplot format see the Tecplot User's guide.\n"
	  << "#\n";
    }

    out << "Variables=x,y,z";

    if (solution_names != NULL)
      for (unsigned int n=0; n<solution_names->size(); n++)
	{
#ifdef LIBMESH_USE_REAL_NUMBERS

	  // Write variable names for real variables
	  out << "," << (*solution_names)[n];

#else

	  // Write variable names for complex variables
	  out << "," << "r_"   << (*solution_names)[n]
	      << "," << "i_"   << (*solution_names)[n]
	      << "," << "a_"   << (*solution_names)[n];

#endif
	}

    out << '\n';

    out << "Zone f=fepoint, n=" << mesh.n_nodes() << ", e=" << mesh.n_active_sub_elem();

    if (mesh.mesh_dimension() == 1)
      out << ", et=lineseg";
    else if (mesh.mesh_dimension() == 2)
      out << ", et=quadrilateral";
    else if (mesh.mesh_dimension() == 3)
      out << ", et=brick";
    else
      {
	// Dimension other than 1, 2, or 3?
	libmesh_error();
      }

    // Use default mesh color = black
    out << ", c=black\n";

  } // finished writing header

  for (unsigned int i=0; i<mesh.n_nodes(); i++)
    {
      // Print the point without a newline
      mesh.point(i).write_unformatted(out, false);

      if ((v != NULL) && (solution_names != NULL))
	{
	  const unsigned int n_vars = solution_names->size();


	  for (unsigned int c=0; c<n_vars; c++)
	    {
#ifdef LIBMESH_USE_REAL_NUMBERS
	      // Write real data
	      out << std::setprecision(this->ascii_precision())
		  << (*v)[i*n_vars + c] << " ";

#else
	      // Write complex data
	      out << std::setprecision(this->ascii_precision())
		  << (*v)[i*n_vars + c].real() << " "
		  << (*v)[i*n_vars + c].imag() << " "
		  << std::abs((*v)[i*n_vars + c]) << " ";

#endif
	    }
	}

      // Write a new line after the data for this node
      out << '\n';
    }

//   const_active_elem_iterator       it (mesh.elements_begin());
//   const const_active_elem_iterator end(mesh.elements_end());

  MeshBase::const_element_iterator       it  = mesh.active_elements_begin();
  const MeshBase::const_element_iterator end = mesh.active_elements_end();

  for ( ; it != end; ++it)
    (*it)->write_connectivity(out, TECPLOT);
}



void TecplotIO::write_binary (const std::string& fname,
			      const std::vector<Number>* vec,
			      const std::vector<std::string>* solution_names)
{
  // Call the ASCII output function if configure did not detect
  // the Tecplot binary API
#ifndef LIBMESH_HAVE_TECPLOT_API

    libMesh::err << "WARNING: Tecplot Binary files require the Tecplot API." << std::endl
	          << "Continuing with ASCII output."
	          << std::endl;

    if (libMesh::processor_id() == 0)
      this->write_ascii (fname, vec, solution_names);
    return;

#else

  // Get a constant reference to the mesh.
  const MeshBase& mesh = MeshOutput<MeshBase>::mesh();

  // Tecplot binary output only good for dim=2,3
  if (mesh.mesh_dimension() == 1)
    {
      this->write_ascii (fname, vec, solution_names);

      return;
    }

  // Required variables
  std::string tecplot_variable_names;
  int is_double =  0,
    tec_debug =  0,
    cell_type = ((mesh.mesh_dimension()==2) ? (1) : (3));

  // Build a string containing all the variable names to pass to Tecplot
  {
    tecplot_variable_names += "x, y, z";

    if (solution_names != NULL)
      {
	for (unsigned int name=0; name<solution_names->size(); name++)
	  {
#ifdef LIBMESH_USE_REAL_NUMBERS

	    tecplot_variable_names += ", ";
	    tecplot_variable_names += (*solution_names)[name];

#else

	    tecplot_variable_names += ", ";
	    tecplot_variable_names += "r_";
	    tecplot_variable_names += (*solution_names)[name];
	    tecplot_variable_names += ", ";
	    tecplot_variable_names += "i_";
	    tecplot_variable_names += (*solution_names)[name];
	    tecplot_variable_names += ", ";
	    tecplot_variable_names += "a_";
	    tecplot_variable_names += (*solution_names)[name];

#endif
	  }
      }
  }

  // Instantiate a TecplotMacros interface.  In 2D the most nodes per
  // face should be 4, in 3D it's 8.


  TecplotMacros tm(mesh.n_nodes(),
#ifdef LIBMESH_USE_REAL_NUMBERS
		   (3 + ((solution_names == NULL) ? 0 : solution_names->size())),
#else
		   (3 + 3*((solution_names == NULL) ? 0 : solution_names->size())),
#endif
		   mesh.n_active_sub_elem(),
		   ((mesh.mesh_dimension() == 2) ? 4 : 8)
		   );


  // Copy the nodes and data to the TecplotMacros class. Note that we store
  // everything as a float here since the eye doesn't require a double to
  // understand what is going on
  for (unsigned int v=0; v<mesh.n_nodes(); v++)
    {
      tm.nd(0,v) = static_cast<float>(mesh.point(v)(0));
      tm.nd(1,v) = static_cast<float>(mesh.point(v)(1));
      tm.nd(2,v) = static_cast<float>(mesh.point(v)(2));

      if ((vec != NULL) &&
	  (solution_names != NULL))
	{
	  const unsigned int n_vars = solution_names->size();

	  for (unsigned int c=0; c<n_vars; c++)
	    {
#ifdef LIBMESH_USE_REAL_NUMBERS

	      tm.nd((3+c),v)     = static_cast<float>((*vec)[v*n_vars + c]);
#else
	      tm.nd((3+3*c),v)   = static_cast<float>((*vec)[v*n_vars + c].real());
	      tm.nd((3+3*c+1),v) = static_cast<float>((*vec)[v*n_vars + c].imag());
	      tm.nd((3+3*c+2),v) = static_cast<float>(std::abs((*vec)[v*n_vars + c]));
#endif
	    }
	}
    }


  // Copy the connectivity
  {
    unsigned int te = 0;

//     const_active_elem_iterator       it (mesh.elements_begin());
//     const const_active_elem_iterator end(mesh.elements_end());

    MeshBase::const_element_iterator       it  = mesh.active_elements_begin();
    const MeshBase::const_element_iterator end = mesh.active_elements_end();

    for ( ; it != end; ++it)
      {
	std::vector<unsigned int> conn;
	for (unsigned int se=0; se<(*it)->n_sub_elem(); se++)
	  {
	    (*it)->connectivity(se, TECPLOT, conn);

	    for (unsigned int node=0; node<conn.size(); node++)
	      tm.cd(node,te) = conn[node];

	    te++;
	  }
      }
  }


  // Ready to call the Tecplot API
  {
    int ierr = 0,
      num_nodes = static_cast<int>(mesh.n_nodes()),
      num_cells = static_cast<int>(mesh.n_active_sub_elem());


    ierr = TECINI (NULL,
		   (char*) tecplot_variable_names.c_str(),
		   (char*) fname.c_str(),
		   (char*) ".",
		   &tec_debug,
		   &is_double);

    libmesh_assert_equal_to (ierr, 0);

    ierr = TECZNE (NULL,
		   &num_nodes,
		   &num_cells,
		   &cell_type,
		   (char*) "FEBLOCK",
		   NULL);

    libmesh_assert_equal_to (ierr, 0);


    int total =
#ifdef LIBMESH_USE_REAL_NUMBERS
      ((3 + ((solution_names == NULL) ? 0 : solution_names->size()))*num_nodes);
#else
      ((3 + 3*((solution_names == NULL) ? 0 : solution_names->size()))*num_nodes);
#endif


    ierr = TECDAT (&total,
		   &tm.nodalData[0],
		   &is_double);

    libmesh_assert_equal_to (ierr, 0);

    ierr = TECNOD (&tm.connData[0]);

    libmesh_assert_equal_to (ierr, 0);

    ierr = TECEND ();

    libmesh_assert_equal_to (ierr, 0);
  }

#endif
}

} // namespace libMesh

