<?php $root=""; ?>
<?php require($root."navigation.php"); ?>
<html>
<head>
  <?php load_style($root); ?>
</head>
 
<body>
 
<?php make_navigation("systems_of_equations_ex4",$root)?>
 
<div class="content">
<a name="comments"></a> 
<div class = "comment">
<h1> Systems Example 4 - Linear Elastic Cantilever </h1>
By David Knezevic

<br><br>In this example we model a homogeneous isotropic cantilever
using the equations of linear elasticity. We set the Poisson ratio to
\nu = 0.3 and clamp the left boundary and apply a vertical load at the
right boundary.


<br><br>

<br><br>C++ include files that we need
</div>

<div class ="fragment">
<pre>
        #include &lt;iostream&gt;
        #include &lt;algorithm&gt;
        #include &lt;math.h&gt;
        
</pre>
</div>
<div class = "comment">
libMesh includes
</div>

<div class ="fragment">
<pre>
        #include "libmesh.h"
        #include "mesh.h"
        #include "mesh_generation.h"
        #include "exodusII_io.h"
        #include "gnuplot_io.h"
        #include "linear_implicit_system.h"
        #include "equation_systems.h"
        #include "fe.h"
        #include "quadrature_gauss.h"
        #include "dof_map.h"
        #include "sparse_matrix.h"
        #include "numeric_vector.h"
        #include "dense_matrix.h"
        #include "dense_submatrix.h"
        #include "dense_vector.h"
        #include "dense_subvector.h"
        #include "perf_log.h"
        #include "elem.h"
        #include "boundary_info.h"
        #include "zero_function.h"
        #include "dirichlet_boundaries.h"
        #include "string_to_enum.h"
        #include "getpot.h"
        
</pre>
</div>
<div class = "comment">
Bring in everything from the libMesh namespace
</div>

<div class ="fragment">
<pre>
        using namespace libMesh;
        
</pre>
</div>
<div class = "comment">
Matrix and right-hand side assemble
</div>

<div class ="fragment">
<pre>
        void assemble_elasticity(EquationSystems& es,
                                 const std::string& system_name);
        
</pre>
</div>
<div class = "comment">
Define the elasticity tensor, which is a fourth-order tensor
i.e. it has four indices i,j,k,l
</div>

<div class ="fragment">
<pre>
        Real eval_elasticity_tensor(unsigned int i,
                                    unsigned int j,
                                    unsigned int k,
                                    unsigned int l);
        
</pre>
</div>
<div class = "comment">
Begin the main program.
</div>

<div class ="fragment">
<pre>
        int main (int argc, char** argv)
        {
</pre>
</div>
<div class = "comment">
Initialize libMesh and any dependent libaries
</div>

<div class ="fragment">
<pre>
          LibMeshInit init (argc, argv);
        
</pre>
</div>
<div class = "comment">
Initialize the cantilever mesh
</div>

<div class ="fragment">
<pre>
          const unsigned int dim = 2;
        
</pre>
</div>
<div class = "comment">
Skip this 2D example if libMesh was compiled as 1D-only.
</div>

<div class ="fragment">
<pre>
          libmesh_example_assert(dim &lt;= LIBMESH_DIM, "2D support");
        
          Mesh mesh(dim);
          MeshTools::Generation::build_square (mesh,
                                               50, 10,
                                               0., 1.,
                                               0., 0.2,
                                               QUAD9);
        
          
</pre>
</div>
<div class = "comment">
Print information about the mesh to the screen.
</div>

<div class ="fragment">
<pre>
          mesh.print_info();
          
          
</pre>
</div>
<div class = "comment">
Create an equation systems object.
</div>

<div class ="fragment">
<pre>
          EquationSystems equation_systems (mesh);
          
</pre>
</div>
<div class = "comment">
Declare the system and its variables.
Create a system named "Elasticity"
</div>

<div class ="fragment">
<pre>
          LinearImplicitSystem& system =
            equation_systems.add_system&lt;LinearImplicitSystem&gt; ("Elasticity");
        
          
</pre>
</div>
<div class = "comment">
Add two displacement variables, u and v, to the system
</div>

<div class ="fragment">
<pre>
          unsigned int u_var = system.add_variable("u", SECOND, LAGRANGE);
          unsigned int v_var = system.add_variable("v", SECOND, LAGRANGE);
        
        
          system.attach_assemble_function (assemble_elasticity);
        
</pre>
</div>
<div class = "comment">
Construct a Dirichlet boundary condition object
We impose a "clamped" boundary condition on the
"left" boundary, i.e. bc_id = 3
</div>

<div class ="fragment">
<pre>
          std::set&lt;boundary_id_type&gt; boundary_ids;
          boundary_ids.insert(3);
        
</pre>
</div>
<div class = "comment">
Create a vector storing the variable numbers which the BC applies to
</div>

<div class ="fragment">
<pre>
          std::vector&lt;unsigned int&gt; variables(2);
          variables[0] = u_var; variables[1] = v_var;
          
</pre>
</div>
<div class = "comment">
Create a ZeroFunction to initialize dirichlet_bc
</div>

<div class ="fragment">
<pre>
          ZeroFunction&lt;&gt; zf;
          
          DirichletBoundary dirichlet_bc(boundary_ids,
                                         variables,
                                         &zf);
        
</pre>
</div>
<div class = "comment">
We must add the Dirichlet boundary condition _before_ 
we call equation_systems.init()
</div>

<div class ="fragment">
<pre>
          system.get_dof_map().add_dirichlet_boundary(dirichlet_bc);
          
</pre>
</div>
<div class = "comment">
Initialize the data structures for the equation system.
</div>

<div class ="fragment">
<pre>
          equation_systems.init();
        
</pre>
</div>
<div class = "comment">
Print information about the system to the screen.
</div>

<div class ="fragment">
<pre>
          equation_systems.print_info();
        
</pre>
</div>
<div class = "comment">
Solve the system
</div>

<div class ="fragment">
<pre>
          system.solve();
        
</pre>
</div>
<div class = "comment">
Plot the solution
</div>

<div class ="fragment">
<pre>
        #ifdef LIBMESH_HAVE_EXODUS_API
          ExodusII_IO (mesh).write_equation_systems("displacement.e",equation_systems);
        #endif // #ifdef LIBMESH_HAVE_EXODUS_API
        
</pre>
</div>
<div class = "comment">
All done.  
</div>

<div class ="fragment">
<pre>
          return 0;
        }
        
        
        void assemble_elasticity(EquationSystems& es,
                                 const std::string& system_name)
        {
          libmesh_assert (system_name == "Elasticity");
          
          const MeshBase& mesh = es.get_mesh();
        
          const unsigned int dim = mesh.mesh_dimension();
        
          LinearImplicitSystem& system = es.get_system&lt;LinearImplicitSystem&gt;("Elasticity");
        
          const unsigned int u_var = system.variable_number ("u");
          const unsigned int v_var = system.variable_number ("v");
        
          const DofMap& dof_map = system.get_dof_map();
          FEType fe_type = dof_map.variable_type(0);
          AutoPtr&lt;FEBase&gt; fe (FEBase::build(dim, fe_type));
          QGauss qrule (dim, fe_type.default_quadrature_order());
          fe-&gt;attach_quadrature_rule (&qrule);
        
          AutoPtr&lt;FEBase&gt; fe_face (FEBase::build(dim, fe_type));
          QGauss qface(dim-1, fe_type.default_quadrature_order());
          fe_face-&gt;attach_quadrature_rule (&qface);
        
          const std::vector&lt;Real&gt;& JxW = fe-&gt;get_JxW();
          const std::vector&lt;std::vector&lt;RealGradient&gt; &gt;& dphi = fe-&gt;get_dphi();
        
          DenseMatrix&lt;Number&gt; Ke;
          DenseVector&lt;Number&gt; Fe;
        
          DenseSubMatrix&lt;Number&gt;
            Kuu(Ke), Kuv(Ke),
            Kvu(Ke), Kvv(Ke);
        
          DenseSubVector&lt;Number&gt;
            Fu(Fe),
            Fv(Fe);
        
          std::vector&lt;unsigned int&gt; dof_indices;
          std::vector&lt;unsigned int&gt; dof_indices_u;
          std::vector&lt;unsigned int&gt; dof_indices_v;
        
          MeshBase::const_element_iterator       el     = mesh.active_local_elements_begin();
          const MeshBase::const_element_iterator end_el = mesh.active_local_elements_end();
        
          for ( ; el != end_el; ++el)
            {
              const Elem* elem = *el;
        
              dof_map.dof_indices (elem, dof_indices);
              dof_map.dof_indices (elem, dof_indices_u, u_var);
              dof_map.dof_indices (elem, dof_indices_v, v_var);
        
              const unsigned int n_dofs   = dof_indices.size();
              const unsigned int n_u_dofs = dof_indices_u.size(); 
              const unsigned int n_v_dofs = dof_indices_v.size();
        
              fe-&gt;reinit (elem);
        
              Ke.resize (n_dofs, n_dofs);
              Fe.resize (n_dofs);
        
              Kuu.reposition (u_var*n_u_dofs, u_var*n_u_dofs, n_u_dofs, n_u_dofs);
              Kuv.reposition (u_var*n_u_dofs, v_var*n_u_dofs, n_u_dofs, n_v_dofs);
              
              Kvu.reposition (v_var*n_v_dofs, u_var*n_v_dofs, n_v_dofs, n_u_dofs);
              Kvv.reposition (v_var*n_v_dofs, v_var*n_v_dofs, n_v_dofs, n_v_dofs);
        
              Fu.reposition (u_var*n_u_dofs, n_u_dofs);
              Fv.reposition (v_var*n_u_dofs, n_v_dofs);
        
              for (unsigned int qp=0; qp&lt;qrule.n_points(); qp++)
              {
                  for (unsigned int i=0; i&lt;n_u_dofs; i++)
                    for (unsigned int j=0; j&lt;n_u_dofs; j++)
                    {
</pre>
</div>
<div class = "comment">
Tensor indices
</div>

<div class ="fragment">
<pre>
                      unsigned int C_i, C_j, C_k, C_l;
                      C_i=0, C_k=0;
        
        
                      C_j=0, C_l=0;
                      Kuu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                      
                      C_j=1, C_l=0;
                      Kuu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
        
                      C_j=0, C_l=1;
                      Kuu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
        
                      C_j=1, C_l=1;
                      Kuu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                    }
        
                  for (unsigned int i=0; i&lt;n_u_dofs; i++)
                    for (unsigned int j=0; j&lt;n_v_dofs; j++)
                    {
</pre>
</div>
<div class = "comment">
Tensor indices
</div>

<div class ="fragment">
<pre>
                      unsigned int C_i, C_j, C_k, C_l;
                      C_i=0, C_k=1;
        
        
                      C_j=0, C_l=0;
                      Kuv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                      
                      C_j=1, C_l=0;
                      Kuv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
        
                      C_j=0, C_l=1;
                      Kuv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
        
                      C_j=1, C_l=1;
                      Kuv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                    }
        
                  for (unsigned int i=0; i&lt;n_v_dofs; i++)
                    for (unsigned int j=0; j&lt;n_u_dofs; j++)
                    {
</pre>
</div>
<div class = "comment">
Tensor indices
</div>

<div class ="fragment">
<pre>
                      unsigned int C_i, C_j, C_k, C_l;
                      C_i=1, C_k=0;
        
        
                      C_j=0, C_l=0;
                      Kvu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                      
                      C_j=1, C_l=0;
                      Kvu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
        
                      C_j=0, C_l=1;
                      Kvu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
        
                      C_j=1, C_l=1;
                      Kvu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                    }
        
                  for (unsigned int i=0; i&lt;n_v_dofs; i++)
                    for (unsigned int j=0; j&lt;n_v_dofs; j++)
                    {
</pre>
</div>
<div class = "comment">
Tensor indices
</div>

<div class ="fragment">
<pre>
                      unsigned int C_i, C_j, C_k, C_l;
                      C_i=1, C_k=1;
        
        
                      C_j=0, C_l=0;
                      Kvv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                      
                      C_j=1, C_l=0;
                      Kvv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
        
                      C_j=0, C_l=1;
                      Kvv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
        
                      C_j=1, C_l=1;
                      Kvv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                    }
              }
        
              {
                for (unsigned int side=0; side&lt;elem-&gt;n_sides(); side++)
                  if (elem-&gt;neighbor(side) == NULL)
                    {
                      boundary_id_type bc_id = mesh.boundary_info-&gt;boundary_id (elem,side);
                      if (bc_id==BoundaryInfo::invalid_id)
                          libmesh_error();
        
                      const std::vector&lt;std::vector&lt;Real&gt; &gt;&  phi_face = fe_face-&gt;get_phi();
                      const std::vector&lt;Real&gt;& JxW_face = fe_face-&gt;get_JxW();
        
                      fe_face-&gt;reinit(elem, side);
        
                      for (unsigned int qp=0; qp&lt;qface.n_points(); qp++)
                      {
                        if( bc_id == 1 ) // Apply a traction on the right side
                        {
                          for (unsigned int i=0; i&lt;n_v_dofs; i++)
                          {
                            Fv(i) += JxW_face[qp]* (-1.) * phi_face[i][qp];
                          }
                        }
                      }
                    }
              } 
        
              dof_map.constrain_element_matrix_and_vector (Ke, Fe, dof_indices);
              
              system.matrix-&gt;add_matrix (Ke, dof_indices);
              system.rhs-&gt;add_vector    (Fe, dof_indices);
            }
        }
        
        Real eval_elasticity_tensor(unsigned int i,
                                    unsigned int j,
                                    unsigned int k,
                                    unsigned int l)
        {
</pre>
</div>
<div class = "comment">
Define the Poisson ratio
</div>

<div class ="fragment">
<pre>
          const Real nu = 0.3;
          
</pre>
</div>
<div class = "comment">
Define the Lame constants (lambda_1 and lambda_2) based on Poisson ratio
</div>

<div class ="fragment">
<pre>
          const Real lambda_1 = nu / ( (1. + nu) * (1. - 2.*nu) );
          const Real lambda_2 = 0.5 / (1 + nu);
        
</pre>
</div>
<div class = "comment">
Define the Kronecker delta functions that we need here
</div>

<div class ="fragment">
<pre>
          Real delta_ij = (i == j) ? 1. : 0.;
          Real delta_il = (i == l) ? 1. : 0.;
          Real delta_ik = (i == k) ? 1. : 0.;
          Real delta_jl = (j == l) ? 1. : 0.;
          Real delta_jk = (j == k) ? 1. : 0.;
          Real delta_kl = (k == l) ? 1. : 0.;
          
          return lambda_1 * delta_ij * delta_kl + lambda_2 * (delta_ik * delta_jl + delta_il * delta_jk);
        }
</pre>
</div>

<a name="nocomments"></a> 
<br><br><br> <h1> The program without comments: </h1> 
<pre> 
  
  
  #include &lt;iostream&gt;
  #include &lt;algorithm&gt;
  #include &lt;math.h&gt;
  
  #include <B><FONT COLOR="#BC8F8F">&quot;libmesh.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_generation.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;exodusII_io.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;gnuplot_io.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;linear_implicit_system.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;equation_systems.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;fe.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;quadrature_gauss.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dof_map.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;sparse_matrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;numeric_vector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_matrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_submatrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_vector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_subvector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;perf_log.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;elem.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;boundary_info.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;zero_function.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dirichlet_boundaries.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;string_to_enum.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;getpot.h&quot;</FONT></B>
  
  using namespace libMesh;
  
  <B><FONT COLOR="#228B22">void</FONT></B> assemble_elasticity(EquationSystems&amp; es,
                           <B><FONT COLOR="#228B22">const</FONT></B> std::string&amp; system_name);
  
  Real eval_elasticity_tensor(<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i,
                              <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j,
                              <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> k,
                              <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> l);
  
  <B><FONT COLOR="#228B22">int</FONT></B> main (<B><FONT COLOR="#228B22">int</FONT></B> argc, <B><FONT COLOR="#228B22">char</FONT></B>** argv)
  {
    LibMeshInit init (argc, argv);
  
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> dim = 2;
  
    libmesh_example_assert(dim &lt;= LIBMESH_DIM, <B><FONT COLOR="#BC8F8F">&quot;2D support&quot;</FONT></B>);
  
    Mesh mesh(dim);
    <B><FONT COLOR="#5F9EA0">MeshTools</FONT></B>::Generation::build_square (mesh,
                                         50, 10,
                                         0., 1.,
                                         0., 0.2,
                                         QUAD9);
  
    
    mesh.print_info();
    
    
    EquationSystems equation_systems (mesh);
    
    LinearImplicitSystem&amp; system =
      equation_systems.add_system&lt;LinearImplicitSystem&gt; (<B><FONT COLOR="#BC8F8F">&quot;Elasticity&quot;</FONT></B>);
  
    
    <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> u_var = system.add_variable(<B><FONT COLOR="#BC8F8F">&quot;u&quot;</FONT></B>, SECOND, LAGRANGE);
    <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> v_var = system.add_variable(<B><FONT COLOR="#BC8F8F">&quot;v&quot;</FONT></B>, SECOND, LAGRANGE);
  
  
    system.attach_assemble_function (assemble_elasticity);
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::set&lt;boundary_id_type&gt; boundary_ids;
    boundary_ids.insert(3);
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B>&gt; variables(2);
    variables[0] = u_var; variables[1] = v_var;
    
    ZeroFunction&lt;&gt; zf;
    
    DirichletBoundary dirichlet_bc(boundary_ids,
                                   variables,
                                   &amp;zf);
  
    system.get_dof_map().add_dirichlet_boundary(dirichlet_bc);
    
    equation_systems.init();
  
    equation_systems.print_info();
  
    system.solve();
  
  #ifdef LIBMESH_HAVE_EXODUS_API
    ExodusII_IO (mesh).write_equation_systems(<B><FONT COLOR="#BC8F8F">&quot;displacement.e&quot;</FONT></B>,equation_systems);
  #endif <I><FONT COLOR="#B22222">// #ifdef LIBMESH_HAVE_EXODUS_API
</FONT></I>  
    <B><FONT COLOR="#A020F0">return</FONT></B> 0;
  }
  
  
  <B><FONT COLOR="#228B22">void</FONT></B> assemble_elasticity(EquationSystems&amp; es,
                           <B><FONT COLOR="#228B22">const</FONT></B> std::string&amp; system_name)
  {
    libmesh_assert (system_name == <B><FONT COLOR="#BC8F8F">&quot;Elasticity&quot;</FONT></B>);
    
    <B><FONT COLOR="#228B22">const</FONT></B> MeshBase&amp; mesh = es.get_mesh();
  
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> dim = mesh.mesh_dimension();
  
    LinearImplicitSystem&amp; system = es.get_system&lt;LinearImplicitSystem&gt;(<B><FONT COLOR="#BC8F8F">&quot;Elasticity&quot;</FONT></B>);
  
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> u_var = system.variable_number (<B><FONT COLOR="#BC8F8F">&quot;u&quot;</FONT></B>);
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> v_var = system.variable_number (<B><FONT COLOR="#BC8F8F">&quot;v&quot;</FONT></B>);
  
    <B><FONT COLOR="#228B22">const</FONT></B> DofMap&amp; dof_map = system.get_dof_map();
    FEType fe_type = dof_map.variable_type(0);
    AutoPtr&lt;FEBase&gt; fe (FEBase::build(dim, fe_type));
    QGauss qrule (dim, fe_type.default_quadrature_order());
    fe-&gt;attach_quadrature_rule (&amp;qrule);
  
    AutoPtr&lt;FEBase&gt; fe_face (FEBase::build(dim, fe_type));
    QGauss qface(dim-1, fe_type.default_quadrature_order());
    fe_face-&gt;attach_quadrature_rule (&amp;qface);
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Real&gt;&amp; JxW = fe-&gt;get_JxW();
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;RealGradient&gt; &gt;&amp; dphi = fe-&gt;get_dphi();
  
    DenseMatrix&lt;Number&gt; Ke;
    DenseVector&lt;Number&gt; Fe;
  
    DenseSubMatrix&lt;Number&gt;
      Kuu(Ke), Kuv(Ke),
      Kvu(Ke), Kvv(Ke);
  
    DenseSubVector&lt;Number&gt;
      Fu(Fe),
      Fv(Fe);
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B>&gt; dof_indices;
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B>&gt; dof_indices_u;
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B>&gt; dof_indices_v;
  
    <B><FONT COLOR="#5F9EA0">MeshBase</FONT></B>::const_element_iterator       el     = mesh.active_local_elements_begin();
    <B><FONT COLOR="#228B22">const</FONT></B> MeshBase::const_element_iterator end_el = mesh.active_local_elements_end();
  
    <B><FONT COLOR="#A020F0">for</FONT></B> ( ; el != end_el; ++el)
      {
        <B><FONT COLOR="#228B22">const</FONT></B> Elem* elem = *el;
  
        dof_map.dof_indices (elem, dof_indices);
        dof_map.dof_indices (elem, dof_indices_u, u_var);
        dof_map.dof_indices (elem, dof_indices_v, v_var);
  
        <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> n_dofs   = dof_indices.size();
        <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> n_u_dofs = dof_indices_u.size(); 
        <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> n_v_dofs = dof_indices_v.size();
  
        fe-&gt;reinit (elem);
  
        Ke.resize (n_dofs, n_dofs);
        Fe.resize (n_dofs);
  
        Kuu.reposition (u_var*n_u_dofs, u_var*n_u_dofs, n_u_dofs, n_u_dofs);
        Kuv.reposition (u_var*n_u_dofs, v_var*n_u_dofs, n_u_dofs, n_v_dofs);
        
        Kvu.reposition (v_var*n_v_dofs, u_var*n_v_dofs, n_v_dofs, n_u_dofs);
        Kvv.reposition (v_var*n_v_dofs, v_var*n_v_dofs, n_v_dofs, n_v_dofs);
  
        Fu.reposition (u_var*n_u_dofs, n_u_dofs);
        Fv.reposition (v_var*n_u_dofs, n_v_dofs);
  
        <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> qp=0; qp&lt;qrule.n_points(); qp++)
        {
            <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;n_u_dofs; i++)
              <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;n_u_dofs; j++)
              {
                <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> C_i, C_j, C_k, C_l;
                C_i=0, C_k=0;
  
  
                C_j=0, C_l=0;
                Kuu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                
                C_j=1, C_l=0;
                Kuu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
  
                C_j=0, C_l=1;
                Kuu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
  
                C_j=1, C_l=1;
                Kuu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
              }
  
            <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;n_u_dofs; i++)
              <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;n_v_dofs; j++)
              {
                <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> C_i, C_j, C_k, C_l;
                C_i=0, C_k=1;
  
  
                C_j=0, C_l=0;
                Kuv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                
                C_j=1, C_l=0;
                Kuv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
  
                C_j=0, C_l=1;
                Kuv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
  
                C_j=1, C_l=1;
                Kuv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
              }
  
            <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;n_v_dofs; i++)
              <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;n_u_dofs; j++)
              {
                <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> C_i, C_j, C_k, C_l;
                C_i=1, C_k=0;
  
  
                C_j=0, C_l=0;
                Kvu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                
                C_j=1, C_l=0;
                Kvu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
  
                C_j=0, C_l=1;
                Kvu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
  
                C_j=1, C_l=1;
                Kvu(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
              }
  
            <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;n_v_dofs; i++)
              <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;n_v_dofs; j++)
              {
                <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> C_i, C_j, C_k, C_l;
                C_i=1, C_k=1;
  
  
                C_j=0, C_l=0;
                Kvv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
                
                C_j=1, C_l=0;
                Kvv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
  
                C_j=0, C_l=1;
                Kvv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
  
                C_j=1, C_l=1;
                Kvv(i,j) += JxW[qp]*(eval_elasticity_tensor(C_i,C_j,C_k,C_l) * dphi[i][qp](C_j)*dphi[j][qp](C_l));
              }
        }
  
        {
          <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> side=0; side&lt;elem-&gt;n_sides(); side++)
            <B><FONT COLOR="#A020F0">if</FONT></B> (elem-&gt;neighbor(side) == NULL)
              {
                boundary_id_type bc_id = mesh.boundary_info-&gt;boundary_id (elem,side);
                <B><FONT COLOR="#A020F0">if</FONT></B> (bc_id==BoundaryInfo::invalid_id)
                    libmesh_error();
  
                <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;Real&gt; &gt;&amp;  phi_face = fe_face-&gt;get_phi();
                <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Real&gt;&amp; JxW_face = fe_face-&gt;get_JxW();
  
                fe_face-&gt;reinit(elem, side);
  
                <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> qp=0; qp&lt;qface.n_points(); qp++)
                {
                  <B><FONT COLOR="#A020F0">if</FONT></B>( bc_id == 1 ) <I><FONT COLOR="#B22222">// Apply a traction on the right side
</FONT></I>                  {
                    <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;n_v_dofs; i++)
                    {
                      Fv(i) += JxW_face[qp]* (-1.) * phi_face[i][qp];
                    }
                  }
                }
              }
        } 
  
        dof_map.constrain_element_matrix_and_vector (Ke, Fe, dof_indices);
        
        system.matrix-&gt;add_matrix (Ke, dof_indices);
        system.rhs-&gt;add_vector    (Fe, dof_indices);
      }
  }
  
  Real eval_elasticity_tensor(<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i,
                              <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j,
                              <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> k,
                              <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> l)
  {
    <B><FONT COLOR="#228B22">const</FONT></B> Real nu = 0.3;
    
    <B><FONT COLOR="#228B22">const</FONT></B> Real lambda_1 = nu / ( (1. + nu) * (1. - 2.*nu) );
    <B><FONT COLOR="#228B22">const</FONT></B> Real lambda_2 = 0.5 / (1 + nu);
  
    Real delta_ij = (i == j) ? 1. : 0.;
    Real delta_il = (i == l) ? 1. : 0.;
    Real delta_ik = (i == k) ? 1. : 0.;
    Real delta_jl = (j == l) ? 1. : 0.;
    Real delta_jk = (j == k) ? 1. : 0.;
    Real delta_kl = (k == l) ? 1. : 0.;
    
    <B><FONT COLOR="#A020F0">return</FONT></B> lambda_1 * delta_ij * delta_kl + lambda_2 * (delta_ik * delta_jl + delta_il * delta_jk);
  }
</pre> 
<a name="output"></a> 
<br><br><br> <h1> The console output of the program: </h1> 
<pre>
Linking systems_of_equations_ex4-opt...
***************************************************************
* Running  mpirun -np 6 ./systems_of_equations_ex4-opt -ksp_type cg
***************************************************************
 
 Mesh Information:
  mesh_dimension()=2
  spatial_dimension()=3
  n_nodes()=2121
    n_local_nodes()=371
  n_elem()=500
    n_local_elem()=83
    n_active_elem()=500
  n_subdomains()=1
  n_partitions()=6
  n_processors()=6
  n_threads()=1
  processor_id()=0

 EquationSystems
  n_systems()=1
   System #0, "Elasticity"
    Type "LinearImplicit"
    Variables="u" "v" 
    Finite Element Types="LAGRANGE", "JACOBI_20_00" "LAGRANGE", "JACOBI_20_00" 
    Infinite Element Mapping="CARTESIAN" "CARTESIAN" 
    Approximation Orders="SECOND", "THIRD" "SECOND", "THIRD" 
    n_dofs()=4242
    n_local_dofs()=742
    n_constrained_dofs()=43
    n_local_constrained_dofs()=0
    n_vectors()=1
    n_matrices()=1
    DofMap Sparsity
      Average  On-Processor Bandwidth <= 29.504
      Average Off-Processor Bandwidth <= 0.916442
      Maximum  On-Processor Bandwidth <= 50
      Maximum Off-Processor Bandwidth <= 28
    DofMap Constraints
      Number of DoF Constraints = 42
      Average DoF Constraint Length= 0
      Number of Node Constraints = 0


-------------------------------------------------------------------
| Processor id:   0                                                |
| Num Processors: 6                                                |
| Time:           Fri Aug 24 15:27:42 2012                         |
| OS:             Linux                                            |
| HostName:       daedalus                                         |
| OS Release:     2.6.32-34-generic                                |
| OS Version:     #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011      |
| Machine:        x86_64                                           |
| Username:       roystgnr                                         |
| Configuration:  ./configure run on Wed Aug 22 12:44:06 CDT 2012  |
-------------------------------------------------------------------
 ----------------------------------------------------------------------------------------------------------------
| libMesh Performance: Alive time=0.242877, Active time=0.165758                                                 |
 ----------------------------------------------------------------------------------------------------------------
| Event                              nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                              w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|----------------------------------------------------------------------------------------------------------------|
|                                                                                                                |
|                                                                                                                |
| DofMap                                                                                                         |
|   add_neighbors_to_send_list()     1         0.0001      0.000106    0.0001      0.000129    0.06     0.08     |
|   build_constraint_matrix()        83        0.0000      0.000000    0.0000      0.000000    0.02     0.02     |
|   build_sparsity()                 1         0.0016      0.001639    0.0020      0.001965    0.99     1.19     |
|   cnstrn_elem_mat_vec()            83        0.0000      0.000000    0.0000      0.000000    0.01     0.01     |
|   create_dof_constraints()         1         0.0018      0.001771    0.0027      0.002709    1.07     1.63     |
|   distribute_dofs()                1         0.0006      0.000603    0.0035      0.003523    0.36     2.13     |
|   dof_indices()                    1937      0.0009      0.000000    0.0009      0.000000    0.57     0.57     |
|   prepare_send_list()              1         0.0000      0.000008    0.0000      0.000008    0.00     0.00     |
|   reinit()                         1         0.0011      0.001071    0.0011      0.001071    0.65     0.65     |
|                                                                                                                |
| EquationSystems                                                                                                |
|   build_solution_vector()          1         0.0004      0.000361    0.0013      0.001315    0.22     0.79     |
|                                                                                                                |
| ExodusII_IO                                                                                                    |
|   write_nodal_data()               1         0.0018      0.001816    0.0018      0.001816    1.10     1.10     |
|                                                                                                                |
| FE                                                                                                             |
|   compute_shape_functions()        130       0.0002      0.000002    0.0002      0.000002    0.12     0.12     |
|   init_shape_functions()           48        0.0000      0.000001    0.0000      0.000001    0.02     0.02     |
|   inverse_map()                    141       0.0002      0.000002    0.0002      0.000002    0.15     0.15     |
|                                                                                                                |
| FEMap                                                                                                          |
|   compute_affine_map()             130       0.0002      0.000001    0.0002      0.000001    0.09     0.09     |
|   compute_face_map()               47        0.0002      0.000005    0.0005      0.000010    0.13     0.29     |
|   init_face_shape_functions()      21        0.0000      0.000001    0.0000      0.000001    0.02     0.02     |
|   init_reference_to_physical_map() 48        0.0002      0.000005    0.0002      0.000005    0.13     0.13     |
|                                                                                                                |
| Mesh                                                                                                           |
|   find_neighbors()                 1         0.0004      0.000418    0.0015      0.001525    0.25     0.92     |
|   renumber_nodes_and_elem()        2         0.0001      0.000055    0.0001      0.000055    0.07     0.07     |
|                                                                                                                |
| MeshCommunication                                                                                              |
|   compute_hilbert_indices()        2         0.0019      0.000974    0.0019      0.000974    1.18     1.18     |
|   find_global_indices()            2         0.0003      0.000129    0.0066      0.003283    0.16     3.96     |
|   parallel_sort()                  2         0.0011      0.000559    0.0036      0.001811    0.68     2.19     |
|                                                                                                                |
| MeshOutput                                                                                                     |
|   write_equation_systems()         1         0.0000      0.000035    0.0032      0.003166    0.02     1.91     |
|                                                                                                                |
| MeshTools::Generation                                                                                          |
|   build_cube()                     1         0.0004      0.000406    0.0004      0.000406    0.24     0.24     |
|                                                                                                                |
| MetisPartitioner                                                                                               |
|   partition()                      1         0.0015      0.001473    0.0042      0.004229    0.89     2.55     |
|                                                                                                                |
| Parallel                                                                                                       |
|   allgather()                      8         0.0018      0.000219    0.0018      0.000219    1.06     1.06     |
|   broadcast()                      1         0.0000      0.000012    0.0000      0.000012    0.01     0.01     |
|   gather()                         1         0.0000      0.000041    0.0000      0.000041    0.02     0.02     |
|   max(scalar)                      2         0.0031      0.001547    0.0031      0.001547    1.87     1.87     |
|   max(vector)                      2         0.0000      0.000022    0.0000      0.000022    0.03     0.03     |
|   min(vector)                      2         0.0003      0.000139    0.0003      0.000139    0.17     0.17     |
|   probe()                          50        0.0019      0.000038    0.0019      0.000038    1.16     1.16     |
|   receive()                        50        0.0001      0.000002    0.0020      0.000041    0.06     1.22     |
|   send()                           50        0.0001      0.000002    0.0001      0.000002    0.05     0.05     |
|   send_receive()                   54        0.0001      0.000003    0.0023      0.000042    0.08     1.38     |
|   sum()                            10        0.0034      0.000341    0.0034      0.000341    2.06     2.06     |
|                                                                                                                |
| Parallel::Request                                                                                              |
|   wait()                           50        0.0000      0.000001    0.0000      0.000001    0.02     0.02     |
|                                                                                                                |
| Partitioner                                                                                                    |
|   set_node_processor_ids()         1         0.0002      0.000182    0.0014      0.001423    0.11     0.86     |
|   set_parent_processor_ids()       1         0.0000      0.000045    0.0000      0.000045    0.03     0.03     |
|                                                                                                                |
| PetscLinearSolver                                                                                              |
|   solve()                          1         0.1297      0.129655    0.1297      0.129655    78.22    78.22    |
|                                                                                                                |
| System                                                                                                         |
|   assemble()                       1         0.0097      0.009736    0.0106      0.010642    5.87     6.42     |
 ----------------------------------------------------------------------------------------------------------------
| Totals:                            2972      0.1658                                          100.00            |
 ----------------------------------------------------------------------------------------------------------------

 
***************************************************************
* Done Running  mpirun -np 6 ./systems_of_equations_ex4-opt -ksp_type cg
***************************************************************
</pre>
</div>
<?php make_footer() ?>
</body>
</html>
<?php if (0) { ?>
\#Local Variables:
\#mode: html
\#End:
<?php } ?>
