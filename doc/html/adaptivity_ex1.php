<?php $root=""; ?>
<?php require($root."navigation.php"); ?>
<html>
<head>
  <?php load_style($root); ?>
</head>
 
<body>
 
<?php make_navigation("adaptivity_ex1",$root)?>
 
<div class="content">
<a name="comments"></a> 
<div class = "comment">
<h1>Adaptivity Example 1 - Solving 1D PDE Using Adaptive Mesh Refinement</h1>

<br><br>This example demonstrates how to solve a simple 1D problem
using adaptive mesh refinement. The PDE that is solved is:
-epsilon*u''(x) + u(x) = 1, on the domain [0,1] with boundary conditions 
u(0) = u(1) = 0 and where epsilon << 1.

<br><br>The approach used to solve 1D problems in libMesh is virtually identical to
solving 2D or 3D problems, so in this sense this example represents a good
starting point for new users. Note that many concepts are used in this 
example which are explained more fully in subsequent examples.


<br><br>Libmesh includes
</div>

<div class ="fragment">
<pre>
        #include "mesh.h"
        #include "mesh_generation.h"
        #include "edge_edge3.h"
        #include "gnuplot_io.h"
        #include "equation_systems.h"
        #include "linear_implicit_system.h"
        #include "fe.h"
        #include "getpot.h"
        #include "quadrature_gauss.h"
        #include "sparse_matrix.h"
        #include "dof_map.h"
        #include "numeric_vector.h"
        #include "dense_matrix.h"
        #include "dense_vector.h"
        #include "error_vector.h"
        #include "kelly_error_estimator.h"
        #include "mesh_refinement.h"
        
</pre>
</div>
<div class = "comment">
Bring in everything from the libMesh namespace
</div>

<div class ="fragment">
<pre>
        using namespace libMesh;
        
        void assemble_1D(EquationSystems& es, const std::string& system_name);
        
        int main(int argc, char** argv)
        {   
</pre>
</div>
<div class = "comment">
Initialize the library.  This is necessary because the library
may depend on a number of other libraries (i.e. MPI and PETSc)
that require initialization before use.  When the LibMeshInit
object goes out of scope, other libraries and resources are
finalized.
</div>

<div class ="fragment">
<pre>
          LibMeshInit init (argc, argv);
        
</pre>
</div>
<div class = "comment">
Skip adaptive examples on a non-adaptive libMesh build
</div>

<div class ="fragment">
<pre>
        #ifndef LIBMESH_ENABLE_AMR
          libmesh_example_assert(false, "--enable-amr");
        #else
        
</pre>
</div>
<div class = "comment">
Create a new mesh
</div>

<div class ="fragment">
<pre>
          Mesh mesh;
        
          GetPot command_line (argc, argv);
        
          int n = 4;
          if ( command_line.search(1, "-n") )
            n = command_line.next(n);
        
</pre>
</div>
<div class = "comment">
Build a 1D mesh with 4 elements from x=0 to x=1, using 
EDGE3 (i.e. quadratic) 1D elements. They are called EDGE3 elements
because a quadratic element contains 3 nodes.
</div>

<div class ="fragment">
<pre>
          MeshTools::Generation::build_line(mesh,n,0.,1.,EDGE3);
        
</pre>
</div>
<div class = "comment">
Define the equation systems object and the system we are going
to solve. See Introduction Example 2 for more details.
</div>

<div class ="fragment">
<pre>
          EquationSystems equation_systems(mesh);
          LinearImplicitSystem& system = equation_systems.add_system
            &lt;LinearImplicitSystem&gt;("1D");
        
</pre>
</div>
<div class = "comment">
Add a variable "u" to the system, using second-order approximation
</div>

<div class ="fragment">
<pre>
          system.add_variable("u",SECOND);
        
</pre>
</div>
<div class = "comment">
Give the system a pointer to the matrix assembly function. This 
will be called when needed by the library.
</div>

<div class ="fragment">
<pre>
          system.attach_assemble_function(assemble_1D);
        
</pre>
</div>
<div class = "comment">
Define the mesh refinement object that takes care of adaptively
refining the mesh.
</div>

<div class ="fragment">
<pre>
          MeshRefinement mesh_refinement(mesh);
        
</pre>
</div>
<div class = "comment">
These parameters determine the proportion of elements that will
be refined and coarsened. Any element within 30% of the maximum 
error on any element will be refined, and any element within 30% 
of the minimum error on any element might be coarsened
</div>

<div class ="fragment">
<pre>
          mesh_refinement.refine_fraction()  = 0.7;
          mesh_refinement.coarsen_fraction() = 0.3;
</pre>
</div>
<div class = "comment">
We won't refine any element more than 5 times in total
</div>

<div class ="fragment">
<pre>
          mesh_refinement.max_h_level()      = 5;
        
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
Refinement parameters
</div>

<div class ="fragment">
<pre>
          const unsigned int max_r_steps = 5; // Refine the mesh 5 times
        
</pre>
</div>
<div class = "comment">
Define the refinement loop
</div>

<div class ="fragment">
<pre>
          for(unsigned int r_step=0; r_step&lt;=max_r_steps; r_step++)
            {
</pre>
</div>
<div class = "comment">
Solve the equation system
</div>

<div class ="fragment">
<pre>
              equation_systems.get_system("1D").solve();
        
</pre>
</div>
<div class = "comment">
We need to ensure that the mesh is not refined on the last iteration
of this loop, since we do not want to refine the mesh unless we are
going to solve the equation system for that refined mesh.
</div>

<div class ="fragment">
<pre>
              if(r_step != max_r_steps)
                {
</pre>
</div>
<div class = "comment">
Error estimation objects, see Adaptivity Example 2 for details
</div>

<div class ="fragment">
<pre>
                  ErrorVector error;
                  KellyErrorEstimator error_estimator;
        
</pre>
</div>
<div class = "comment">
Compute the error for each active element
</div>

<div class ="fragment">
<pre>
                  error_estimator.estimate_error(system, error);
        
</pre>
</div>
<div class = "comment">
Flag elements to be refined and coarsened
</div>

<div class ="fragment">
<pre>
                  mesh_refinement.flag_elements_by_error_fraction (error);
        
</pre>
</div>
<div class = "comment">
Perform refinement and coarsening
</div>

<div class ="fragment">
<pre>
                  mesh_refinement.refine_and_coarsen_elements();
        
</pre>
</div>
<div class = "comment">
Reinitialize the equation_systems object for the newly refined
mesh. One of the steps in this is project the solution onto the 
new mesh
</div>

<div class ="fragment">
<pre>
                  equation_systems.reinit();
                }
            }
        
</pre>
</div>
<div class = "comment">
Construct gnuplot plotting object, pass in mesh, title of plot
and boolean to indicate use of grid in plot. The grid is used to
show the edges of each element in the mesh.
</div>

<div class ="fragment">
<pre>
          GnuPlotIO plot(mesh,"Adaptivity Example 1", GnuPlotIO::GRID_ON);
        
</pre>
</div>
<div class = "comment">
Write out script to be called from within gnuplot:
Load gnuplot, then type "call 'gnuplot_script'" from gnuplot prompt
</div>

<div class ="fragment">
<pre>
          plot.write_equation_systems("gnuplot_script",equation_systems);
        #endif // #ifndef LIBMESH_ENABLE_AMR
          
</pre>
</div>
<div class = "comment">
All done.  libMesh objects are destroyed here.  Because the
LibMeshInit object was created first, its destruction occurs
last, and it's destructor finalizes any external libraries and
checks for leaked memory.
</div>

<div class ="fragment">
<pre>
          return 0;
        }
        
        
        
        
</pre>
</div>
<div class = "comment">
Define the matrix assembly function for the 1D PDE we are solving
</div>

<div class ="fragment">
<pre>
        void assemble_1D(EquationSystems& es, const std::string& system_name)
        {
        
        #ifdef LIBMESH_ENABLE_AMR
        
</pre>
</div>
<div class = "comment">
It is a good idea to check we are solving the correct system
</div>

<div class ="fragment">
<pre>
          libmesh_assert(system_name == "1D");
        
</pre>
</div>
<div class = "comment">
Get a reference to the mesh object
</div>

<div class ="fragment">
<pre>
          const MeshBase& mesh = es.get_mesh();
        
</pre>
</div>
<div class = "comment">
The dimension we are using, i.e. dim==1
</div>

<div class ="fragment">
<pre>
          const unsigned int dim = mesh.mesh_dimension();
        
</pre>
</div>
<div class = "comment">
Get a reference to the system we are solving
</div>

<div class ="fragment">
<pre>
          LinearImplicitSystem& system = es.get_system&lt;LinearImplicitSystem&gt;("1D");
        
</pre>
</div>
<div class = "comment">
Get a reference to the DofMap object for this system. The DofMap object
handles the index translation from node and element numbers to degree of
freedom numbers. DofMap's are discussed in more detail in future examples.
</div>

<div class ="fragment">
<pre>
          const DofMap& dof_map = system.get_dof_map();
        
</pre>
</div>
<div class = "comment">
Get a constant reference to the Finite Element type for the first 
(and only) variable in the system.
</div>

<div class ="fragment">
<pre>
          FEType fe_type = dof_map.variable_type(0);
        
</pre>
</div>
<div class = "comment">
Build a finite element object of the specified type. The build
function dynamically allocates memory so we use an AutoPtr in this case.
An AutoPtr is a pointer that cleans up after itself. See examples 3 and 4
for more details on AutoPtr.
</div>

<div class ="fragment">
<pre>
          AutoPtr&lt;FEBase&gt; fe(FEBase::build(dim, fe_type));
        
</pre>
</div>
<div class = "comment">
Tell the finite element object to use fifth order Gaussian quadrature
</div>

<div class ="fragment">
<pre>
          QGauss qrule(dim,FIFTH);
          fe-&gt;attach_quadrature_rule(&qrule);
        
</pre>
</div>
<div class = "comment">
Here we define some references to cell-specific data that will be used to 
assemble the linear system.


<br><br>The element Jacobian * quadrature weight at each integration point.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;Real&gt;& JxW = fe-&gt;get_JxW();
        
</pre>
</div>
<div class = "comment">
The element shape functions evaluated at the quadrature points.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;std::vector&lt;Real&gt; &gt;& phi = fe-&gt;get_phi();
        
</pre>
</div>
<div class = "comment">
The element shape function gradients evaluated at the quadrature points.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;std::vector&lt;RealGradient&gt; &gt;& dphi = fe-&gt;get_dphi();
        
</pre>
</div>
<div class = "comment">
Declare a dense matrix and dense vector to hold the element matrix
and right-hand-side contribution
</div>

<div class ="fragment">
<pre>
          DenseMatrix&lt;Number&gt; Ke;
          DenseVector&lt;Number&gt; Fe;
        
</pre>
</div>
<div class = "comment">
This vector will hold the degree of freedom indices for the element.
These define where in the global system the element degrees of freedom
get mapped.
</div>

<div class ="fragment">
<pre>
          std::vector&lt;unsigned int&gt; dof_indices;
        
</pre>
</div>
<div class = "comment">
We now loop over all the active elements in the mesh in order to calculate
the matrix and right-hand-side contribution from each element. Use a
const_element_iterator to loop over the elements. We make
el_end const as it is used only for the stopping condition of the loop.
</div>

<div class ="fragment">
<pre>
          MeshBase::const_element_iterator el     = mesh.active_local_elements_begin();
          const MeshBase::const_element_iterator el_end = mesh.active_local_elements_end();
        
</pre>
</div>
<div class = "comment">
Note that ++el is preferred to el++ when using loops with iterators
</div>

<div class ="fragment">
<pre>
          for( ; el != el_end; ++el)
          {
</pre>
</div>
<div class = "comment">
It is convenient to store a pointer to the current element
</div>

<div class ="fragment">
<pre>
            const Elem* elem = *el;
        
</pre>
</div>
<div class = "comment">
Get the degree of freedom indices for the current element. 
These define where in the global matrix and right-hand-side this 
element will contribute to.
</div>

<div class ="fragment">
<pre>
            dof_map.dof_indices(elem, dof_indices);
        
</pre>
</div>
<div class = "comment">
Compute the element-specific data for the current element. This 
involves computing the location of the quadrature points (q_point) 
and the shape functions (phi, dphi) for the current element.
</div>

<div class ="fragment">
<pre>
            fe-&gt;reinit(elem);
        
</pre>
</div>
<div class = "comment">
Store the number of local degrees of freedom contained in this element
</div>

<div class ="fragment">
<pre>
            const int n_dofs = dof_indices.size();
        
</pre>
</div>
<div class = "comment">
We resize and zero out Ke and Fe (resize() also clears the matrix and
vector). In this example, all elements in the mesh are EDGE3's, so 
Ke will always be 3x3, and Fe will always be 3x1. If the mesh contained
different element types, then the size of Ke and Fe would change.
</div>

<div class ="fragment">
<pre>
            Ke.resize(n_dofs, n_dofs);
            Fe.resize(n_dofs);
        
        
</pre>
</div>
<div class = "comment">
Now loop over quadrature points to handle numerical integration
</div>

<div class ="fragment">
<pre>
            for(unsigned int qp=0; qp&lt;qrule.n_points(); qp++)
            {
</pre>
</div>
<div class = "comment">
Now build the element matrix and right-hand-side using loops to
integrate the test functions (i) against the trial functions (j).
</div>

<div class ="fragment">
<pre>
              for(unsigned int i=0; i&lt;phi.size(); i++)
              {
                Fe(i) += JxW[qp]*phi[i][qp];
        
                for(unsigned int j=0; j&lt;phi.size(); j++)
                {
                  Ke(i,j) += JxW[qp]*(1.e-3*dphi[i][qp]*dphi[j][qp] + 
                                             phi[i][qp]*phi[j][qp]);
                }
              }
            }
        
        
</pre>
</div>
<div class = "comment">
At this point we have completed the matrix and RHS summation. The
final step is to apply boundary conditions, which in this case are
simple Dirichlet conditions with u(0) = u(1) = 0.


<br><br>Define the penalty parameter used to enforce the BC's
</div>

<div class ="fragment">
<pre>
            double penalty = 1.e10;
        
</pre>
</div>
<div class = "comment">
Loop over the sides of this element. For a 1D element, the "sides"
are defined as the nodes on each edge of the element, i.e. 1D elements
have 2 sides.
</div>

<div class ="fragment">
<pre>
            for(unsigned int s=0; s&lt;elem-&gt;n_sides(); s++)
            {
</pre>
</div>
<div class = "comment">
If this element has a NULL neighbor, then it is on the edge of the
mesh and we need to enforce a boundary condition using the penalty
method.
</div>

<div class ="fragment">
<pre>
              if(elem-&gt;neighbor(s) == NULL)
              {
                Ke(s,s) += penalty;
                Fe(s)   += 0*penalty;
              }
            }
        
</pre>
</div>
<div class = "comment">
This is a function call that is necessary when using adaptive
mesh refinement. See Adaptivity Example 2 for more details.
</div>

<div class ="fragment">
<pre>
            dof_map.constrain_element_matrix_and_vector (Ke, Fe, dof_indices);
        
</pre>
</div>
<div class = "comment">
Add Ke and Fe to the global matrix and right-hand-side.
</div>

<div class ="fragment">
<pre>
            system.matrix-&gt;add_matrix(Ke, dof_indices);
            system.rhs-&gt;add_vector(Fe, dof_indices);
          }
        #endif // #ifdef LIBMESH_ENABLE_AMR
        }
</pre>
</div>

<a name="nocomments"></a> 
<br><br><br> <h1> The program without comments: </h1> 
<pre> 
  
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_generation.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;edge_edge3.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;gnuplot_io.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;equation_systems.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;linear_implicit_system.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;fe.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;getpot.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;quadrature_gauss.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;sparse_matrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dof_map.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;numeric_vector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_matrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_vector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;error_vector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;kelly_error_estimator.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_refinement.h&quot;</FONT></B>
  
  using namespace libMesh;
  
  <B><FONT COLOR="#228B22">void</FONT></B> assemble_1D(EquationSystems&amp; es, <B><FONT COLOR="#228B22">const</FONT></B> std::string&amp; system_name);
  
  <B><FONT COLOR="#228B22">int</FONT></B> main(<B><FONT COLOR="#228B22">int</FONT></B> argc, <B><FONT COLOR="#228B22">char</FONT></B>** argv)
  {   
    LibMeshInit init (argc, argv);
  
  #ifndef LIBMESH_ENABLE_AMR
    libmesh_example_assert(false, <B><FONT COLOR="#BC8F8F">&quot;--enable-amr&quot;</FONT></B>);
  #<B><FONT COLOR="#A020F0">else</FONT></B>
  
    Mesh mesh;
  
    GetPot command_line (argc, argv);
  
    <B><FONT COLOR="#228B22">int</FONT></B> n = 4;
    <B><FONT COLOR="#A020F0">if</FONT></B> ( command_line.search(1, <B><FONT COLOR="#BC8F8F">&quot;-n&quot;</FONT></B>) )
      n = command_line.next(n);
  
    <B><FONT COLOR="#5F9EA0">MeshTools</FONT></B>::Generation::build_line(mesh,n,0.,1.,EDGE3);
  
    EquationSystems equation_systems(mesh);
    LinearImplicitSystem&amp; system = equation_systems.add_system
      &lt;LinearImplicitSystem&gt;(<B><FONT COLOR="#BC8F8F">&quot;1D&quot;</FONT></B>);
  
    system.add_variable(<B><FONT COLOR="#BC8F8F">&quot;u&quot;</FONT></B>,SECOND);
  
    system.attach_assemble_function(assemble_1D);
  
    MeshRefinement mesh_refinement(mesh);
  
    mesh_refinement.refine_fraction()  = 0.7;
    mesh_refinement.coarsen_fraction() = 0.3;
    mesh_refinement.max_h_level()      = 5;
  
    equation_systems.init();
  
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> max_r_steps = 5; <I><FONT COLOR="#B22222">// Refine the mesh 5 times
</FONT></I>  
    <B><FONT COLOR="#A020F0">for</FONT></B>(<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> r_step=0; r_step&lt;=max_r_steps; r_step++)
      {
        equation_systems.get_system(<B><FONT COLOR="#BC8F8F">&quot;1D&quot;</FONT></B>).solve();
  
        <B><FONT COLOR="#A020F0">if</FONT></B>(r_step != max_r_steps)
          {
            ErrorVector error;
            KellyErrorEstimator error_estimator;
  
            error_estimator.estimate_error(system, error);
  
            mesh_refinement.flag_elements_by_error_fraction (error);
  
            mesh_refinement.refine_and_coarsen_elements();
  
            equation_systems.reinit();
          }
      }
  
    GnuPlotIO plot(mesh,<B><FONT COLOR="#BC8F8F">&quot;Adaptivity Example 1&quot;</FONT></B>, GnuPlotIO::GRID_ON);
  
    plot.write_equation_systems(<B><FONT COLOR="#BC8F8F">&quot;gnuplot_script&quot;</FONT></B>,equation_systems);
  #endif <I><FONT COLOR="#B22222">// #ifndef LIBMESH_ENABLE_AMR
</FONT></I>    
    <B><FONT COLOR="#A020F0">return</FONT></B> 0;
  }
  
  
  
  
  <B><FONT COLOR="#228B22">void</FONT></B> assemble_1D(EquationSystems&amp; es, <B><FONT COLOR="#228B22">const</FONT></B> std::string&amp; system_name)
  {
  
  #ifdef LIBMESH_ENABLE_AMR
  
    libmesh_assert(system_name == <B><FONT COLOR="#BC8F8F">&quot;1D&quot;</FONT></B>);
  
    <B><FONT COLOR="#228B22">const</FONT></B> MeshBase&amp; mesh = es.get_mesh();
  
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> dim = mesh.mesh_dimension();
  
    LinearImplicitSystem&amp; system = es.get_system&lt;LinearImplicitSystem&gt;(<B><FONT COLOR="#BC8F8F">&quot;1D&quot;</FONT></B>);
  
    <B><FONT COLOR="#228B22">const</FONT></B> DofMap&amp; dof_map = system.get_dof_map();
  
    FEType fe_type = dof_map.variable_type(0);
  
    AutoPtr&lt;FEBase&gt; fe(FEBase::build(dim, fe_type));
  
    QGauss qrule(dim,FIFTH);
    fe-&gt;attach_quadrature_rule(&amp;qrule);
  
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Real&gt;&amp; JxW = fe-&gt;get_JxW();
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;Real&gt; &gt;&amp; phi = fe-&gt;get_phi();
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;RealGradient&gt; &gt;&amp; dphi = fe-&gt;get_dphi();
  
    DenseMatrix&lt;Number&gt; Ke;
    DenseVector&lt;Number&gt; Fe;
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B>&gt; dof_indices;
  
    <B><FONT COLOR="#5F9EA0">MeshBase</FONT></B>::const_element_iterator el     = mesh.active_local_elements_begin();
    <B><FONT COLOR="#228B22">const</FONT></B> MeshBase::const_element_iterator el_end = mesh.active_local_elements_end();
  
    <B><FONT COLOR="#A020F0">for</FONT></B>( ; el != el_end; ++el)
    {
      <B><FONT COLOR="#228B22">const</FONT></B> Elem* elem = *el;
  
      dof_map.dof_indices(elem, dof_indices);
  
      fe-&gt;reinit(elem);
  
      <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> n_dofs = dof_indices.size();
  
      Ke.resize(n_dofs, n_dofs);
      Fe.resize(n_dofs);
  
  
      <B><FONT COLOR="#A020F0">for</FONT></B>(<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> qp=0; qp&lt;qrule.n_points(); qp++)
      {
        <B><FONT COLOR="#A020F0">for</FONT></B>(<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi.size(); i++)
        {
          Fe(i) += JxW[qp]*phi[i][qp];
  
          <B><FONT COLOR="#A020F0">for</FONT></B>(<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;phi.size(); j++)
          {
            Ke(i,j) += JxW[qp]*(1.e-3*dphi[i][qp]*dphi[j][qp] + 
                                       phi[i][qp]*phi[j][qp]);
          }
        }
      }
  
  
  
      <B><FONT COLOR="#228B22">double</FONT></B> penalty = 1.e10;
  
      <B><FONT COLOR="#A020F0">for</FONT></B>(<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> s=0; s&lt;elem-&gt;n_sides(); s++)
      {
        <B><FONT COLOR="#A020F0">if</FONT></B>(elem-&gt;neighbor(s) == NULL)
        {
          Ke(s,s) += penalty;
          Fe(s)   += 0*penalty;
        }
      }
  
      dof_map.constrain_element_matrix_and_vector (Ke, Fe, dof_indices);
  
      system.matrix-&gt;add_matrix(Ke, dof_indices);
      system.rhs-&gt;add_vector(Fe, dof_indices);
    }
  #endif <I><FONT COLOR="#B22222">// #ifdef LIBMESH_ENABLE_AMR
</FONT></I>  }
</pre> 
<a name="output"></a> 
<br><br><br> <h1> The console output of the program: </h1> 
<pre>
Linking adaptivity_ex1-opt...
***************************************************************
* Running Example  mpirun -np 6 ./adaptivity_ex1-opt -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary
***************************************************************
 
************************************************************************************************************************
***             WIDEN YOUR WINDOW TO 120 CHARACTERS.  Use 'enscript -r -fCourier9' to print this document            ***
************************************************************************************************************************

---------------------------------------------- PETSc Performance Summary: ----------------------------------------------

./adaptivity_ex1-opt on a intel-11. named daedalus with 6 processors, by roystgnr Fri Aug 24 15:20:15 2012
Using Petsc Release Version 3.1.0, Patch 5, Mon Sep 27 11:51:54 CDT 2010

                         Max       Max/Min        Avg      Total 
Time (sec):           1.268e-01      2.29106   6.742e-02
Objects:              2.860e+02      1.02878   2.837e+02
Flops:                1.917e+04      1.27632   1.660e+04  9.960e+04
Flops/sec:            3.179e+05      2.10241   2.665e+05  1.599e+06
MPI Messages:         2.475e+02      1.33423   2.162e+02  1.297e+03
MPI Message Lengths:  2.880e+03      1.30317   1.179e+01  1.529e+04
MPI Reductions:       5.240e+02      1.01550

Flop counting convention: 1 flop = 1 real number operation of type (multiply/divide/add/subtract)
                            e.g., VecAXPY() for real vectors of length N --> 2N flops
                            and VecAXPY() for complex vectors of length N --> 8N flops

Summary of Stages:   ----- Time ------  ----- Flops -----  --- Messages ---  -- Message Lengths --  -- Reductions --
                        Avg     %Total     Avg     %Total   counts   %Total     Avg         %Total   counts   %Total 
 0:      Main Stage: 6.7378e-02  99.9%  9.9597e+04 100.0%  1.297e+03 100.0%  1.179e+01      100.0%  4.107e+02  78.4% 

------------------------------------------------------------------------------------------------------------------------
See the 'Profiling' chapter of the users' manual for details on interpreting output.
Phase summary info:
   Count: number of times phase was executed
   Time and Flops: Max - maximum over all processors
                   Ratio - ratio of maximum to minimum over all processors
   Mess: number of messages sent
   Avg. len: average message length
   Reduct: number of global reductions
   Global: entire computation
   Stage: stages of a computation. Set stages with PetscLogStagePush() and PetscLogStagePop().
      %T - percent time in this phase         %F - percent flops in this phase
      %M - percent messages in this phase     %L - percent message lengths in this phase
      %R - percent reductions in this phase
   Total Mflop/s: 10e-6 * (sum of flops over all processors)/(max time over all processors)
------------------------------------------------------------------------------------------------------------------------
Event                Count      Time (sec)     Flops                             --- Global ---  --- Stage ---   Total
                   Max Ratio  Max     Ratio   Max  Ratio  Mess   Avg len Reduct  %T %F %M %L %R  %T %F %M %L %R Mflop/s
------------------------------------------------------------------------------------------------------------------------

--- Event Stage 0: Main Stage

VecMDot               55 1.0 8.1420e-04 1.9 4.41e+03 1.3 0.0e+00 0.0e+00 5.5e+01  1 23  0  0 10   1 23  0  0 13    28
VecNorm               67 1.0 4.0972e-03 4.6 1.01e+03 1.3 0.0e+00 0.0e+00 6.7e+01  5  5  0  0 13   5  5  0  0 16     1
VecScale              61 1.0 5.0545e-05 1.9 4.61e+02 1.3 0.0e+00 0.0e+00 0.0e+00  0  2  0  0  0   0  2  0  0  0    47
VecCopy               39 1.0 1.5497e-05 3.1 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecSet               101 1.0 3.0518e-05 2.3 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecAXPY               12 1.0 7.2112e-03 1.1 1.68e+02 1.3 0.0e+00 0.0e+00 0.0e+00 10  1  0  0  0  10  1  0  0  0     0
VecMAXPY              61 1.0 1.0967e-05 1.1 5.54e+03 1.3 0.0e+00 0.0e+00 0.0e+00  0 29  0  0  0   0 29  0  0  0  2637
VecAssemblyBegin      53 1.0 5.0912e-03 1.2 0.00e+00 0.0 1.1e+02 6.0e+00 1.3e+02  7  0  8  4 25   7  0  8  4 31     0
VecAssemblyEnd        53 1.0 6.6996e-05 1.5 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecScatterBegin       82 1.0 2.2817e-04 2.0 0.00e+00 0.0 7.9e+02 1.4e+01 0.0e+00  0  0 61 73  0   0  0 61 73  0     0
VecScatterEnd         82 1.0 3.4957e-0322.9 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  1  0  0  0  0   1  0  0  0  0     0
VecNormalize          61 1.0 4.1447e-03 4.4 1.38e+03 1.3 0.0e+00 0.0e+00 6.1e+01  5  7  0  0 12   5  7  0  0 15     2
MatMult               61 1.0 3.3383e-0312.6 3.19e+03 1.3 5.9e+02 1.2e+01 0.0e+00  1 16 45 46  0   1 16 45 46  0     5
MatSolve              61 1.1 2.9802e-05 2.0 3.97e+03 1.3 0.0e+00 0.0e+00 0.0e+00  0 21  0  0  0   0 21  0  0  0   698
MatLUFactorNum         6 1.0 4.8876e-05 1.9 4.63e+02 1.5 0.0e+00 0.0e+00 0.0e+00  0  2  0  0  0   0  2  0  0  0    46
MatILUFactorSym        6 1.0 1.0800e-04 1.4 0.00e+00 0.0 0.0e+00 0.0e+00 6.0e+00  0  0  0  0  1   0  0  0  0  1     0
MatAssemblyBegin      12 1.0 1.6747e-03 1.3 0.00e+00 0.0 8.4e+01 1.7e+01 2.4e+01  2  0  6 10  5   2  0  6 10  6     0
MatAssemblyEnd        12 1.0 2.1729e-03 1.0 0.00e+00 0.0 1.1e+02 5.0e+00 4.8e+01  3  0  9  4  9   3  0  9  4 12     0
MatGetRowIJ            6 1.2 1.0252e-05 2.4 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
MatGetOrdering         6 1.2 1.2231e-04 1.7 0.00e+00 0.0 0.0e+00 0.0e+00 1.9e+01  0  0  0  0  4   0  0  0  0  5     0
MatZeroEntries        18 1.0 1.4067e-05 1.6 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPGMRESOrthog        55 1.0 8.6093e-04 1.8 9.12e+03 1.3 0.0e+00 0.0e+00 5.5e+01  1 48  0  0 10   1 48  0  0 13    55
KSPSetup              12 1.0 4.2772e-04 3.5 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPSolve               6 1.0 1.3527e-02 1.0 1.92e+04 1.3 5.9e+02 1.2e+01 1.5e+02 20100 45 46 28  20100 45 46 36     7
PCSetUp               12 1.0 1.4136e-03 2.0 4.63e+02 1.5 0.0e+00 0.0e+00 2.6e+01  1  2  0  0  5   1  2  0  0  6     2
PCSetUpOnBlocks        6 1.0 4.2152e-04 1.3 4.63e+02 1.5 0.0e+00 0.0e+00 2.6e+01  1  2  0  0  5   1  2  0  0  6     5
PCApply               61 1.0 4.4560e-04 1.3 3.97e+03 1.3 0.0e+00 0.0e+00 0.0e+00  1 21  0  0  0   1 21  0  0  0    47
------------------------------------------------------------------------------------------------------------------------

Memory usage is given in bytes:

Object Type          Creations   Destructions     Memory  Descendants' Mem.
Reports information only for process 0.

--- Event Stage 0: Main Stage

                 Vec   158            158       219680     0
         Vec Scatter    17             17        14756     0
           Index Set    57             57        30384     0
   IS L to G Mapping     6              6         2656     0
              Matrix    24             24        58456     0
       Krylov Solver    12             12       113280     0
      Preconditioner    12             12         8448     0
========================================================================================================================
Average time to get PetscTime(): 0
Average time for MPI_Barrier(): 4.37737e-05
Average time for zero size MPI_Send(): 3.55244e-05
#PETSc Option Table entries:
-ksp_right_pc
-log_summary
-pc_type bjacobi
-sub_pc_factor_levels 4
-sub_pc_factor_zeropivot 0
-sub_pc_type ilu
#End of PETSc Option Table entries
Compiled without FORTRAN kernels
Compiled with full precision matrices (default)
sizeof(short) 2 sizeof(int) 4 sizeof(long) 8 sizeof(void*) 8 sizeof(PetscScalar) 8
Configure run at: Sat May 19 03:47:23 2012
Configure options: --with-debugging=false --COPTFLAGS=-O3 --CXXOPTFLAGS=-O3 --FOPTFLAGS=-O3 --with-clanguage=C++ --with-shared=1 --with-shared-libraries=1 --with-mpi-dir=/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid --with-mumps=true --download-mumps=1 --with-parmetis=true --download-parmetis=1 --with-superlu=true --download-superlu=1 --with-superludir=true --download-superlu_dist=1 --with-blacs=true --download-blacs=1 --with-scalapack=true --download-scalapack=1 --with-hypre=true --download-hypre=1 --with-blas-lib="[/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_intel_lp64.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_sequential.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_core.so]" --with-lapack-lib=/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_solver_lp64_sequential.a
-----------------------------------------
Libraries compiled on Sat May 19 03:47:23 CDT 2012 on daedalus 
Machine characteristics: Linux daedalus 2.6.32-34-generic #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011 x86_64 GNU/Linux 
Using PETSc directory: /org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5
Using PETSc arch: intel-11.1-lucid-mpich2-1.4.1-cxx-opt
-----------------------------------------
Using C compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3   -fPIC   
Using Fortran compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3    
-----------------------------------------
Using include paths: -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/include  
------------------------------------------
Using C linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3 
Using Fortran linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3  
Using libraries: -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lpetsc       -lX11 -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lHYPRE -lcmumps -ldmumps -lsmumps -lzmumps -lmumps_common -lpord -lscalapack -lblacs -lsuperlu_dist_2.4 -lparmetis -lmetis -lsuperlu_4.0 -Wl,-rpath,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -L/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -lmkl_solver_lp64_sequential -lmkl_intel_lp64 -lmkl_sequential -lmkl_core -ldl -Wl,-rpath,/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -L/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -lmpich -lopa -lmpl -lrt -lpthread -Wl,-rpath,/opt/intel/Compiler/11.1/073/lib/intel64 -L/opt/intel/Compiler/11.1/073/lib/intel64 -Wl,-rpath,/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -L/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -lmpichf90 -lifport -lifcore -lm -lm -lmpichcxx -lstdc++ -lmpichcxx -lstdc++ -ldl -lmpich -lopa -lmpl -lrt -lpthread -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -ldl  
------------------------------------------

-------------------------------------------------------------------
| Processor id:   0                                                |
| Num Processors: 6                                                |
| Time:           Fri Aug 24 15:20:15 2012                         |
| OS:             Linux                                            |
| HostName:       daedalus                                         |
| OS Release:     2.6.32-34-generic                                |
| OS Version:     #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011      |
| Machine:        x86_64                                           |
| Username:       roystgnr                                         |
| Configuration:  ./configure run on Wed Aug 22 12:44:06 CDT 2012  |
-------------------------------------------------------------------
 ----------------------------------------------------------------------------------------------------------------
| libMesh Performance: Alive time=0.347044, Active time=0.114865                                                 |
 ----------------------------------------------------------------------------------------------------------------
| Event                              nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                              w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|----------------------------------------------------------------------------------------------------------------|
|                                                                                                                |
|                                                                                                                |
| DofMap                                                                                                         |
|   add_neighbors_to_send_list()     6         0.0001      0.000010    0.0001      0.000013    0.05     0.07     |
|   build_sparsity()                 6         0.0002      0.000035    0.0002      0.000041    0.18     0.21     |
|   create_dof_constraints()         6         0.0000      0.000000    0.0000      0.000000    0.00     0.00     |
|   distribute_dofs()                6         0.0003      0.000044    0.0030      0.000493    0.23     2.57     |
|   dof_indices()                    184       0.0001      0.000000    0.0001      0.000000    0.08     0.08     |
|   old_dof_indices()                34        0.0000      0.000000    0.0000      0.000000    0.01     0.01     |
|   prepare_send_list()              6         0.0000      0.000000    0.0000      0.000000    0.00     0.00     |
|   reinit()                         6         0.0002      0.000028    0.0002      0.000028    0.15     0.15     |
|                                                                                                                |
| EquationSystems                                                                                                |
|   build_solution_vector()          1         0.0000      0.000046    0.0008      0.000758    0.04     0.66     |
|                                                                                                                |
| FE                                                                                                             |
|   compute_shape_functions()        40        0.0000      0.000001    0.0000      0.000001    0.03     0.03     |
|   init_shape_functions()           28        0.0000      0.000002    0.0000      0.000002    0.04     0.04     |
|   inverse_map()                    47        0.0000      0.000000    0.0000      0.000000    0.02     0.02     |
|                                                                                                                |
| FEMap                                                                                                          |
|   compute_affine_map()             40        0.0000      0.000001    0.0000      0.000001    0.03     0.03     |
|   compute_face_map()               11        0.0000      0.000001    0.0000      0.000001    0.01     0.01     |
|   init_face_shape_functions()      5         0.0000      0.000002    0.0000      0.000002    0.01     0.01     |
|   init_reference_to_physical_map() 28        0.0000      0.000001    0.0000      0.000001    0.04     0.04     |
|                                                                                                                |
| GnuPlotIO                                                                                                      |
|   write_nodal_data()               1         0.0713      0.071334    0.0713      0.071334    62.10    62.10    |
|                                                                                                                |
| JumpErrorEstimator                                                                                             |
|   estimate_error()                 5         0.0002      0.000049    0.0018      0.000368    0.21     1.60     |
|                                                                                                                |
| LocationMap                                                                                                    |
|   find()                           76        0.0000      0.000001    0.0000      0.000001    0.03     0.03     |
|   init()                           10        0.0001      0.000006    0.0001      0.000006    0.06     0.06     |
|                                                                                                                |
| Mesh                                                                                                           |
|   contract()                       5         0.0000      0.000003    0.0000      0.000005    0.01     0.02     |
|   find_neighbors()                 6         0.0003      0.000044    0.0006      0.000102    0.23     0.53     |
|   renumber_nodes_and_elem()        17        0.0000      0.000003    0.0000      0.000003    0.04     0.04     |
|                                                                                                                |
| MeshCommunication                                                                                              |
|   compute_hilbert_indices()        7         0.0003      0.000042    0.0003      0.000042    0.25     0.25     |
|   find_global_indices()            7         0.0002      0.000026    0.0055      0.000780    0.16     4.75     |
|   parallel_sort()                  7         0.0025      0.000352    0.0035      0.000495    2.15     3.02     |
|                                                                                                                |
| MeshOutput                                                                                                     |
|   write_equation_systems()         1         0.0000      0.000033    0.0721      0.072126    0.03     62.79    |
|                                                                                                                |
| MeshRefinement                                                                                                 |
|   _coarsen_elements()              10        0.0000      0.000002    0.0002      0.000019    0.02     0.17     |
|   _refine_elements()               10        0.0002      0.000018    0.0020      0.000202    0.16     1.76     |
|   add_point()                      76        0.0001      0.000001    0.0001      0.000001    0.06     0.10     |
|   make_coarsening_compatible()     11        0.0002      0.000017    0.0002      0.000017    0.17     0.17     |
|   make_refinement_compatible()     11        0.0000      0.000002    0.0001      0.000010    0.02     0.09     |
|                                                                                                                |
| MeshTools::Generation                                                                                          |
|   build_cube()                     1         0.0000      0.000038    0.0000      0.000038    0.03     0.03     |
|                                                                                                                |
| MetisPartitioner                                                                                               |
|   partition()                      6         0.0008      0.000128    0.0044      0.000735    0.67     3.84     |
|                                                                                                                |
| Parallel                                                                                                       |
|   allgather()                      32        0.0015      0.000048    0.0015      0.000048    1.33     1.33     |
|   max(bool)                        36        0.0020      0.000057    0.0020      0.000057    1.78     1.78     |
|   max(scalar)                      17        0.0006      0.000034    0.0006      0.000034    0.50     0.50     |
|   max(vector)                      7         0.0001      0.000013    0.0001      0.000013    0.08     0.08     |
|   min(bool)                        22        0.0003      0.000013    0.0003      0.000013    0.26     0.26     |
|   min(scalar)                      5         0.0000      0.000003    0.0000      0.000003    0.01     0.01     |
|   min(vector)                      7         0.0002      0.000025    0.0002      0.000025    0.15     0.15     |
|   probe()                          250       0.0056      0.000022    0.0056      0.000022    4.83     4.83     |
|   receive()                        250       0.0003      0.000001    0.0059      0.000023    0.25     5.10     |
|   send()                           250       0.0001      0.000001    0.0001      0.000001    0.12     0.12     |
|   send_receive()                   264       0.0005      0.000002    0.0066      0.000025    0.41     5.75     |
|   sum()                            34        0.0032      0.000096    0.0032      0.000096    2.83     2.83     |
|                                                                                                                |
| Parallel::Request                                                                                              |
|   wait()                           250       0.0001      0.000000    0.0001      0.000000    0.07     0.07     |
|                                                                                                                |
| Partitioner                                                                                                    |
|   set_node_processor_ids()         6         0.0001      0.000020    0.0042      0.000708    0.11     3.70     |
|   set_parent_processor_ids()       6         0.0000      0.000005    0.0000      0.000005    0.02     0.02     |
|                                                                                                                |
| PetscLinearSolver                                                                                              |
|   solve()                          6         0.0185      0.003090    0.0185      0.003090    16.14    16.14    |
|                                                                                                                |
| ProjectVector                                                                                                  |
|   operator()                       5         0.0001      0.000029    0.0002      0.000037    0.13     0.16     |
|                                                                                                                |
| System                                                                                                         |
|   assemble()                       6         0.0002      0.000032    0.0003      0.000049    0.17     0.26     |
|   project_vector()                 5         0.0040      0.000803    0.0043      0.000855    3.49     3.72     |
 ----------------------------------------------------------------------------------------------------------------
| Totals:                            2179      0.1149                                          100.00            |
 ----------------------------------------------------------------------------------------------------------------

 
***************************************************************
* Done Running Example  mpirun -np 6 ./adaptivity_ex1-opt -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary
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
